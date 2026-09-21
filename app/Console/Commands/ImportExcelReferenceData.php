<?php

namespace App\Console\Commands;

use Database\Seeders\Concerns\GuardsAgainstNonTestDatabase;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use ZipArchive;

/**
 * Загружает .xlsx, экспортированные dbForge (Excel2007) с реальной БД (victory), в таблицы
 * легаси-схемы REVELATION на локальной тестовой Oracle XE — например, чтобы заменить
 * придуманные значения из ReferenceDataSeeder настоящими.
 *
 * Имя файла = имя таблицы (без учёта регистра), первая строка каждого листа — имена колонок.
 * Особенности формата dbForge (см. .det-шаблон, DataExportOptions):
 *  - NullText="null" — NULL записан буквальным текстом "null", а не пустой ячейкой;
 *  - дата/время хранится как число (серийный номер Excel) — распознаётся здесь по ИМЕНИ
 *    колонки (оканчивается на _DATE), а не по формату ячейки: readCell()-фильтр ниже не
 *    даёт PhpSpreadsheet вообще читать стили, а без этого формат ячейки не узнать;
 *  - большие таблицы (например CATALOGUE_CONTENT, ~60 МБ) dbForge режет на несколько листов
 *    ("Part 1", "Part 2", ...), поэтому читаем ВСЕ листы, не только активный.
 *
 * Полная загрузка файла целиком через PhpSpreadsheet::load() требует памяти на порядок
 * больше размера файла (десятки объектов на ячейку) — для CATALOGUE_CONTENT не хватало и
 * 3 ГБ. Вместо этого здесь: (1) число строк на лист узнаётся из сырого XML внутри .xlsx
 * (ZipArchive, только тег <dimension>, без разбора ячеек) и (2) сами данные читаются
 * порциями через IReadFilter — PhpSpreadsheet не создаёт объекты ячеек за пределами
 * текущего диапазона строк, поэтому память не растёт с размером файла.
 *
 * Отформатированная как 'Y-m-d H:i:s' PHP-строка вставляется как обычный бинд-параметр без
 * TO_DATE(): OracleConnection сам выставляет NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS' на
 * сессию при подключении (см. Oci8Connection::getDateFormat()), Oracle конвертирует неявно.
 *
 * Первая колонка — первичный ключ (везде в этой схеме так: TYPE_ID/STATUS_ID/NODE_ID/
 * CATEGORY_ID/ITEM_ID...); загрузка — upsert по этому ключу, а не TRUNCATE+INSERT: TRUNCATE
 * отказывает с ORA-02266, если на таблицу смотрит хоть один включённый FK (например BATCH ->
 * BATCH_STATUS, где уже есть стаб-строка BATCH_ID=-1 из TestClientDataSeeder).
 *
 *   php artisan import:excel "C:\Users\Pax\Desktop\catalog"
 *   php artisan import:excel "C:\Users\Pax\Desktop\reference"
 */
class ImportExcelReferenceData extends Command
{
    use GuardsAgainstNonTestDatabase;

    protected const CHUNK_SIZE = 3000;

    /** @var string[] */
    protected array $failures = [];

    protected $signature = 'import:excel
        {path : Папка с .xlsx (один файл = одна таблица) или путь к одному файлу}
        {--connection=oracle}';

    protected $description = 'Импорт .xlsx (экспорт dbForge) в таблицы легаси-схемы на локальной тестовой Oracle XE';

    public function handle(): int
    {
        $connectionName = (string) $this->option('connection');

        $this->guardTestDatabaseOnly($connectionName);

        $path = $this->argument('path');

        $files = is_dir($path)
            ? collect(glob(rtrim($path, '\\/').DIRECTORY_SEPARATOR.'*.xlsx'))
            : collect(is_file($path) ? [$path] : []);

        if ($files->isEmpty()) {
            $this->error("Нет .xlsx файлов по пути: {$path}");

            return self::FAILURE;
        }

        ini_set('memory_limit', '1536M');

        $db = DB::connection($connectionName);

        foreach ($files as $file) {
            $this->importFile($db, $file);
        }

        if ($this->failures) {
            $this->newLine();
            $this->warn(count($this->failures).' строк(и) не загрузились (см. ниже) — остальные данные загружены:');
            foreach ($this->failures as $failure) {
                $this->line("  - {$failure}");
            }
        }

        return self::SUCCESS;
    }

    protected function importFile(ConnectionInterface $db, string $file): void
    {
        $table = pathinfo($file, PATHINFO_FILENAME);
        $sheetRowCounts = $this->probeSheetRowCounts($file);

        // Заголовок читаем с первого листа отдельным лёгким проходом (фильтр пропускает
        // только строку 1) — по нему же определяем, какие колонки — даты.
        $columns = $this->readHeaderRow($file);
        $dateColumns = [];
        foreach ($columns as $i => $name) {
            if (str_ends_with(strtoupper($name), '_DATE')) {
                $dateColumns[$i] = true;
            }
        }
        $keyColumn = $columns[0];

        $totalRows = array_sum($sheetRowCounts);
        $this->info("→ {$table}: ".count($sheetRowCounts)." лист(ов), ~{$totalRows} строк, ключ {$keyColumn}, колонки: ".implode(', ', $columns));

        $processed = 0;

        foreach ($sheetRowCounts as $sheetIndex => $highestRow) {
            for ($start = 2; $start <= $highestRow; $start += self::CHUNK_SIZE) {
                $end = min($start + self::CHUNK_SIZE - 1, $highestRow);
                $processed += $this->importChunk($db, $file, $table, $sheetIndex, $start, $end, $columns, $dateColumns, $keyColumn);
            }
        }

        $this->info("  готово: {$processed} строк(и) загружено в {$table}");
    }

    /**
     * Число строк на каждом листе .xlsx — напрямую из тега <dimension> в сыром XML листа,
     * без разбора ячеек через PhpSpreadsheet (см. класс-докблок). Физические имена файлов
     * листов НЕ всегда идут по порядку sheet1.xml/sheet2.xml/... — у dbForge, например,
     * единственный лист может называться sheet2.xml (sheet1.xml просто не существует в
     * архиве). Настоящий порядок листов задаёт xl/workbook.xml (тот же порядок, что и у
     * PhpSpreadsheet::getAllSheets()/getSheet($i)), а какому физическому файлу соответствует
     * каждый <sheet> — таблица r:id -> Target в xl/_rels/workbook.xml.rels.
     *
     * @return array<int, int> индекс листа (порядок как в workbook.xml) => номер последней строки с данными
     */
    protected function probeSheetRowCounts(string $file): array
    {
        $zip = new ZipArchive();
        $zip->open($file);

        // Обычный regex здесь ненадёжен: dbForge пишет workbook.xml с префиксом узла (<x:sheet>
        // вместо <sheet>), r:id — не "rId1", а произвольная GUID-строка, а в rels-файле
        // Target идёт ПЕРЕД Id и с ведущим слешем ("/xl/worksheets/sheet2.xml"). SimpleXML +
        // local-name() устойчив ко всем этим вариациям сразу.
        $workbook = new \SimpleXMLElement((string) $zip->getFromName('xl/workbook.xml'));
        $rels = new \SimpleXMLElement((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
        $relationshipsNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $targetByRid = [];
        foreach ($rels->xpath('//*[local-name()="Relationship"]') as $rel) {
            $attrs = $rel->attributes();
            $target = ltrim((string) $attrs['Target'], '/');
            $target = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            $targetByRid[(string) $attrs['Id']] = $target;
        }

        $counts = [];

        foreach ($workbook->xpath('//*[local-name()="sheet"]') as $sheetIndex => $sheetEl) {
            $rid = (string) $sheetEl->attributes($relationshipsNs)['id'];
            $entryName = $targetByRid[$rid] ?? null;

            if ($entryName === null) {
                throw new \RuntimeException("Лист с r:id={$rid} не нашёлся в workbook.xml.rels файла {$file}.");
            }
            $stream = $zip->getStream($entryName);

            if ($stream === false) {
                throw new \RuntimeException("Лист {$entryName} упомянут в workbook.xml, но отсутствует в архиве {$file}.");
            }

            $head = fread($stream, 4096);
            fclose($stream);

            if (!preg_match('/<[A-Za-z0-9]*:?dimension ref="[A-Za-z]+\d+:[A-Za-z]+(\d+)"/', $head, $m)) {
                throw new \RuntimeException("Не нашёл <dimension> в {$entryName} файла {$file} — формат экспорта изменился?");
            }

            $counts[$sheetIndex] = (int) $m[1];
        }

        $zip->close();

        return $counts;
    }

    /** @return string[] */
    protected function readHeaderRow(string $file): array
    {
        $filter = new class implements IReadFilter {
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
            {
                return $row === 1;
            }
        };

        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($filter);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet(0);

        $lastColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn(1));
        $columns = [];

        for ($col = 1; $col <= $lastColumnIndex; $col++) {
            $columns[] = (string) $sheet->getCell(Coordinate::stringFromColumnIndex($col).'1')->getValue();
        }

        $spreadsheet->disconnectWorksheets();

        return $columns;
    }

    /**
     * @param string[] $columns
     * @param array<int, bool> $dateColumns
     * @return int число обработанных строк в этом диапазоне
     */
    protected function importChunk(
        ConnectionInterface $db,
        string $file,
        string $table,
        int $sheetIndex,
        int $startRow,
        int $endRow,
        array $columns,
        array $dateColumns,
        string $keyColumn,
    ): int {
        $filter = new class($sheetIndex, $startRow, $endRow) implements IReadFilter {
            public function __construct(
                private readonly int $sheetIndex,
                private readonly int $startRow,
                private readonly int $endRow,
            ) {
            }

            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
            {
                return $row >= $this->startRow && $row <= $this->endRow;
            }
        };

        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($filter);
        // dbForge на некоторых экспортах кладёт лист по абсолютному индексу, даже если он не
        // единственный — грузим весь файл (фильтр всё равно вырезает лишние строки), а нужный
        // лист берём по индексу явно.
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet($sheetIndex);

        $count = 0;

        for ($row = $startRow; $row <= $endRow; $row++) {
            $record = [];

            foreach ($columns as $i => $name) {
                $value = $sheet->getCell(Coordinate::stringFromColumnIndex($i + 1).$row)->getValue();
                $record[$name] = $this->normalizeValue($value, isset($dateColumns[$i]));
            }

            // Пустая строка (за пределами реальных данных листа, если dimension чуть шире
            // фактических данных) — пропускаем, а не падаем на NOT NULL колонке.
            if ($record[$keyColumn] === null) {
                continue;
            }

            try {
                $db->table($table)->updateOrInsert([$keyColumn => $record[$keyColumn]], $record);
                $count++;
            } catch (\Throwable $e) {
                // Легаси-схема на реальной БД местами шире, чем на локальной тестовой копии
                // (например VARCHAR2 в БАЙТАХ, а не символах — кириллица вроде "КРШ" даёт
                // 6 байт при лимите 5) — не бросаем всю загрузку из-за одной строки, копим и
                // печатаем в конце, остальные строки грузим дальше.
                $firstLine = strtok($e->getMessage(), "\n");
                $this->failures[] = "{$table}.{$keyColumn}={$record[$keyColumn]}: {$firstLine}";
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $count;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function normalizeValue($value, bool $isDateColumn)
    {
        if ($value === null || $value === 'null') {
            return null;
        }

        if ($isDateColumn && is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
        }

        return $value;
    }
}

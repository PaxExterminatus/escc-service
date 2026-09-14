<?php

namespace App\Domain\App\Invoice\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\JcTable;

/**
 * Тестовый шаблон счёта — до появления реального бланка от заказчика. Один и тот же
 * ${invoices}...${/invoices} блок используется и для счёта по одному контейнеру
 * (клонируется один раз), и для пачки счетов за день (клонируется по числу контейнеров).
 */
class InvoiceTemplateBuilder
{
    public function build(string $path): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        $section->addText('${invoices}');

        $section->addText('Счёт № ${invoice_number} от ${invoice_date}', ['bold' => true, 'size' => 13]);
        $section->addTextBreak();

        $section->addText('Клиент: ${client_name}');
        $section->addText('Код клиента: ${client_code}');
        $section->addTextBreak();

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
        ]);

        $table->addRow();
        $table->addCell(7000)->addText('Наименование', ['bold' => true]);
        $table->addCell(2500)->addText('Сумма, руб.', ['bold' => true], ['alignment' => JcTable::END]);

        $table->addRow();
        $table->addCell(7000)->addText('${item_name}');
        $table->addCell(2500)->addText('${item_amount}', [], ['alignment' => JcTable::END]);

        $section->addTextBreak();
        $section->addText('Итого к оплате: ${total_amount} руб.', ['bold' => true, 'size' => 13]);
        $section->addPageBreak();

        $section->addText('${/invoices}');

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
    }
}

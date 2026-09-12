<?php

namespace App\Domain\Messages\Models;

use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * @property int template_id
 * @property string code
 * @property string name
 * @property string body
 * @property bool is_active
 *
 * @mixin \Eloquent
 */
class MessageTemplate extends OracleEloquent
{
    protected $table = 'OPERATOR_MESSAGE_TEMPLATE';
    protected $primaryKey = 'template_id';
    public $sequence = 'OPERATOR_MESSAGE_TEMPLATE_SEQ';
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'body', 'is_active'];

    protected function casts(): array
    {
        return [
            'template_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function render(array $params): string
    {
        $body = $this->body;

        foreach ($params as $key => $value) {
            $body = str_replace('{' . $key . '}', (string) $value, $body);
        }

        return $body;
    }
}

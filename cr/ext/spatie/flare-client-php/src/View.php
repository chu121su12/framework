<?php

namespace Spatie\FlareClient;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class View
{
    protected /*string */$file;

    /** @var array<string, mixed> */
    protected /*array */$data = [];

    /**
     * @param string $file
     * @param array<string, mixed> $data
     */
    public function __construct(/*string */$file, array $data = [])
    {
        $file = backport_type_check('string', $file);

        $this->file = $file;
        $this->data = $data;
    }

    /**
     * @param string $file
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function create(/*string */$file, array $data = [])/*: self*/
    {
        $file = backport_type_check('string', $file);

        return new self($file, $data);
    }

    protected function dumpViewData(/*mixed */$variable)/*: string*/
    {
        $variable = backport_type_check('mixed', $variable);

        $cloner = new VarCloner();

        $dumper = new HtmlDumper();
        $dumper->setDumpHeader('');

        $output = fopen('php://memory', 'r+b');

        if (! $output) {
            return '';
        }

        $dumper->dump($cloner->cloneVar($variable)->withMaxDepth(1), $output, [
            'maxDepth' => 1,
            'maxStringLength' => 160,
        ]);

        return (string)stream_get_contents($output, -1, 0);
    }

    /** @return array<string, mixed> */
    public function toArray()/*: array*/
    {
        return [
            'file' => $this->file,
            'data' => array_map([$this, 'dumpViewData'], $this->data),
        ];
    }
}

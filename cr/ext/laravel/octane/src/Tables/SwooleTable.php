<?php

namespace Laravel\Octane\Tables;

use Swoole\Table;

if (SWOOLE_VERSION_ID === 40804 || SWOOLE_VERSION_ID >= 50000) {
    class SwooleTable extends Table
    {
        use Concerns\EnsuresColumnSizes;

        /**
         * The table columns.
         *
         * @var array
         */
        protected $columns;

        /**
         * Set the data type and size of the columns.
         *
         * @param  string  $name
         * @param  int  $type
         * @param  int  $size
         * @return void
         */
        public function column(/*string */$name, /*int */$type, /*int */$size = 0)/*: bool*/
        {
            $name = cast_to_string($name);
            $type = cast_to_int($type);
            $size = cast_to_int($size);

            $this->columns[$name] = [$type, $size];

            parent::column($name, $type, $size);
        }

        /**
         * Update a row of the table.
         *
         * @param  string  $key
         * @param  array  $values
         * @return void
         */
        public function set(/*string */$key, array $values)/*: bool*/
        {
            $key = cast_to_int($key);

            collect($values)
                ->each($this->ensureColumnsSize());

            parent::set($key, $values);
        }
    }
} else {
    class SwooleTable extends Table
    {
        use Concerns\EnsuresColumnSizes;

        /**
         * The table columns.
         *
         * @var array
         */
        protected $columns;

        /**
         * Set the data type and size of the columns.
         *
         * @param  string  $name
         * @param  int  $type
         * @param  int  $size
         * @return void
         */
        public function column($name, $type, $size = 0)
        {
            $this->columns[$name] = [$type, $size];

            parent::column($name, $type, $size);
        }

        /**
         * Update a row of the table.
         *
         * @param  string  $key
         * @param  array  $values
         * @return void
         */
        public function set($key, array $values)
        {
            collect($values)
                ->each($this->ensureColumnsSize());

            parent::set($key, $values);
        }
    }
}
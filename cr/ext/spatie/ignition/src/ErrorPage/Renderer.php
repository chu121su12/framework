<?php

namespace Spatie\Ignition\ErrorPage;

class Renderer
{
    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function render(array $data, /*string */$viewPath)/*: void*/
    {
        $viewPath = backport_type_check('string', $viewPath);

        $viewFile = $viewPath;

        extract($data, EXTR_OVERWRITE);

        include $viewFile;
    }

    public function renderAsString(array $date, /*string */$viewPath)/*: string*/
    {
        $viewPath = backport_type_check('string', $viewPath);

        ob_start();

        $this->render($date, $viewPath);

        return ob_get_clean();
    }
}

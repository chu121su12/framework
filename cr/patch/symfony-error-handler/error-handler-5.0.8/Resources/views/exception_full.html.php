<!-- <?= $_message = sprintf('%s (%d %s)', $exceptionMessage, $statusCode, $statusText); ?> -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="<?= $this->charset; ?>" />
        <meta name="robots" content="noindex,nofollow" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title><?= $_message; ?></title>
        <link rel="icon" type="image/png" href="<?= $this->doInclude('assets/images/favicon.png.base64'); ?>">
        <style><?= $this->doInclude('assets/css/exception.css'); ?></style>
        <style><?= $this->doInclude('assets/css/exception_full.css'); ?></style>
    </head>
    <body>
        <?php if (class_exists('Symfony\Component\HttpKernel\Kernel')) { ?>
            <header>
                <div class="container">
                    <h1 class="logo"><?= $this->doInclude('assets/images/symfony-logo.svg'); ?> Symfony Exception</h1>

                    <div class="help-link">
                        <a href="https://symfony.com/doc/<?= Symfony\Component\HttpKernel\Kernel::VERSION; ?>/index.html">
                            <span class="icon"><?= $this->doInclude('assets/images/icon-book.svg'); ?></span>
                            <span class="hidden-xs-down">Symfony</span> Docs
                        </a>
                    </div>

                    <div class="help-link">
                        <a href="https://symfony.com/support">
                            <span class="icon"><?= $this->doInclude('assets/images/icon-support.svg'); ?></span>
                            <span class="hidden-xs-down">Symfony</span> Support
                        </a>
                    </div>
                </div>
            </header>
        <?php } ?>

        <?= $this->doInclude('views/exception.html.php', $context); ?>

        <script>
            <?= $this->doInclude('assets/js/exception.js'); ?>
        </script>
    </body>
</html>
<!-- <?= $_message; ?> -->

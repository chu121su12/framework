<?php

namespace Illuminate\Translation;

class CreatesPotentiallyTranslatedStrings_pendingPotentiallyTranslatedString_class extends PotentiallyTranslatedString
        {
            /**
             * The callback to call when the object destructs.
             *
             * @var \Closure
             */
            protected $destructor;

            /**
             * Create a new pending potentially translated string.
             *
             * @param  string  $message
             * @param  \Illuminate\Contracts\Translation\Translator  $translator
             * @param  \Closure  $destructor
             */
            public function __construct($message, $translator, $destructor)
            {
                parent::__construct($message, $translator);

                $this->destructor = $destructor;
            }

            /**
             * Handle the object's destruction.
             *
             * @return void
             */
            public function __destruct()
            {
                $destructor = $this->destructor;

                $destructor($this->toString());
            }
        }

trait CreatesPotentiallyTranslatedStrings
{
    /**
     * Create a pending potentially translated string.
     *
     * @param  string  $attribute
     * @param  string|null  $message
     * @return \Illuminate\Translation\PotentiallyTranslatedString
     */
    protected function pendingPotentiallyTranslatedString($attribute, $message)
    {
        $destructor = $message === null
            ? function ($message) { return $this->messages[] = $message; }
            : function ($message) use ($attribute) { return $this->messages[$attribute] = $message; };

        return new CreatesPotentiallyTranslatedStrings_pendingPotentiallyTranslatedString_class(
            isset($message) ? $message : $attribute, $this->validator->getTranslator(), $destructor
        );
    }
}

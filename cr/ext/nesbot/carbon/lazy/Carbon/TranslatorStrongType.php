<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon;

use Symfony\Component\Translation\MessageCatalogueInterface;

if (!class_exists(LazyTranslator::class, false)) {
    class LazyTranslator extends AbstractTranslator implements TranslatorStrongTypeInterface
    {
        public function trans(/*?string */$id = null, array $parameters = [], /*?string */$domain = null, /*?string */$locale = null)/*: string*/
        {
            $locale = cast_to_string($locale, null);

            $domain = cast_to_string($domain, null);

            $id = cast_to_string($id, null);

            return $this->translate($id, $parameters, $domain, $locale);
        }

        public function getFromCatalogue(MessageCatalogueInterface $catalogue, /*string */$id, /*string */$domain = 'messages')
        {
            $domain = cast_to_string($domain);

            $id = cast_to_string($id);

            $messages = $this->getPrivateProperty($catalogue, 'messages');

            if (isset($messages[$domain.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX][$id])) {
                return $messages[$domain.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX][$id];
            }

            if (isset($messages[$domain][$id])) {
                return $messages[$domain][$id];
            }

            $fallbackCatalogue = $this->getPrivateProperty($catalogue, 'fallbackCatalogue');

            if ($fallbackCatalogue !== null) {
                return $this->getFromCatalogue($fallbackCatalogue, $id, $domain);
            }

            return $id;
        }

        private function getPrivateProperty($instance, /*string */$field)
        {
            $field = cast_to_string($field);

            $function = function (/*string */$field) {
                $field = cast_to_string($field);

                return $this->$field;
            };

            return $function->call($instance, $field);
        }
    }
}

<?php

namespace SilexAutowiring\Injectable;

interface InjectableInterface {
    /**
     * Obtain the wrapped service.
     * @return mixed
     */
    public function get();
}
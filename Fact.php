<?php

class Fact extends FluxModel {
    public int $FactId;
    public string $Content = '';

    public function __construct(int $id = null, string $content = '') {
        $this->FactId = $id;
        $this->Content = $content;
    }
}
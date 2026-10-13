<?php

it('redirects guests from the root to login', function () {
    $this->get('/')
        ->assertRedirect(route('clients.index'));
});
<?php

it('redirects the root to the topics list', function () {
    $response = $this->get('/');

    $response->assertRedirect('/topics');
});

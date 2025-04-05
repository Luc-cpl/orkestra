<?php

use Orkestra\Services\View\HtmlTag;

test('can create an HtmlTag with tag only', function () {
    $tag = new HtmlTag('div');
    expect($tag->tag)->toBe('div');
    expect($tag->attributes)->toBe([]);
    expect($tag->content)->toBe('');
    expect((string) $tag)->toBe('<div></div>');
});

test('can create an HtmlTag with attributes', function () {
    $tag = new HtmlTag('div', ['class' => 'container', 'id' => 'main']);
    expect($tag->tag)->toBe('div');
    expect($tag->attributes)->toBe(['class' => 'container', 'id' => 'main']);
    expect($tag->content)->toBe('');
    expect((string) $tag)->toBe('<div class="container" id="main"></div>');
});

test('can create an HtmlTag with content', function () {
    $tag = new HtmlTag('div', [], 'Hello, world!');
    expect($tag->tag)->toBe('div');
    expect($tag->attributes)->toBe([]);
    expect($tag->content)->toBe('Hello, world!');
    expect((string) $tag)->toBe('<div>Hello, world!</div>');
});

test('can create an HtmlTag with attributes and content', function () {
    $tag = new HtmlTag('div', ['class' => 'container'], 'Hello, world!');
    expect($tag->tag)->toBe('div');
    expect($tag->attributes)->toBe(['class' => 'container']);
    expect($tag->content)->toBe('Hello, world!');
    expect((string) $tag)->toBe('<div class="container">Hello, world!</div>');
});

test('can get an attribute', function () {
    $tag = new HtmlTag('div', ['class' => 'container', 'id' => 'main']);
    expect($tag->getAttribute('class'))->toBe('container');
    expect($tag->getAttribute('id'))->toBe('main');

    // Test for non-existent attribute (linha 21)
    expect($tag->getAttribute('nonexistent'))->toBeNull();
});

test('can set attributes', function () {
    $tag = new HtmlTag('div', ['class' => 'container']);
    $newTag = $tag->setAttributes(['id' => 'main', 'data-test' => 'value']);

    // Original tag should be unchanged
    expect($tag->attributes)->toBe(['class' => 'container']);

    // New tag should have new attributes
    expect($newTag->attributes)->toBe(['id' => 'main', 'data-test' => 'value']);
    expect($newTag->tag)->toBe('div');
    expect($newTag->content)->toBe('');
});

test('can set content', function () {
    $tag = new HtmlTag('div', ['class' => 'container'], 'Original content');
    $newTag = $tag->setContent('New content');

    // Original tag should be unchanged
    expect($tag->content)->toBe('Original content');

    // New tag should have new content
    expect($newTag->content)->toBe('New content');
    expect($newTag->tag)->toBe('div');
    expect($newTag->attributes)->toBe(['class' => 'container']);
});

test('can handle boolean attributes', function () {
    $tag = new HtmlTag('input', ['disabled' => true, 'readonly' => false]);
    expect((string) $tag)->toBe('<input disabled />');
});

test('can handle array attributes', function () {
    $tag = new HtmlTag('div', ['data-config' => ['key1' => 'value1', 'key2' => 'value2']]);
    expect((string) $tag)->toContain('data-config=');
    expect((string) $tag)->toContain(json_encode(['key1' => 'value1', 'key2' => 'value2']));
});

test('renders void tags correctly', function () {
    // Test void tags (linha 42-52)
    $voidTags = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    foreach ($voidTags as $tag) {
        $htmlTag = new HtmlTag($tag);
        expect((string) $htmlTag)->toBe("<{$tag} />");
    }

    // Test with attributes
    $tag = new HtmlTag('img', ['src' => 'image.jpg', 'alt' => 'An image']);
    expect((string) $tag)->toBe('<img src="image.jpg" alt="An image" />');
});

test('renders non-void tags correctly', function () {
    $tag = new HtmlTag('div');
    expect((string) $tag)->toBe('<div></div>');

    $tag = new HtmlTag('span', [], 'Content');
    expect((string) $tag)->toBe('<span>Content</span>');
});

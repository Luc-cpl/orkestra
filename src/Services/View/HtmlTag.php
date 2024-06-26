<?php

namespace Orkestra\Services\View;

use Stringable;

class HtmlTag implements Stringable
{
    /**
     * @param array<string, bool|string|int|float|mixed[]> $attributes
     */
    public function __construct(
        public readonly string $tag,
        public readonly array  $attributes = [],
        public readonly string $content = '',
    ) {
    }

    public function getAttribute(string $string): mixed
    {
        return isset($this->attributes[$string]) ? $this->attributes[$string] : null;
    }

    /**
     * @param array<string, bool|string|int|float|mixed[]> $attributes
     */
    public function setAttributes(array $attributes): self
    {
        return new self($this->tag, $attributes, $this->content);
    }

    public function setContent(string $content): self
    {
        return new self($this->tag, $this->attributes, $content);
    }

    public function __toString(): string
    {
        $attributes = '';
        foreach ($this->attributes as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $attributes .= match ($value) {
                false => '',
                true  => " $key",
                default => sprintf(' %s="%s"', $key, $value),
            };
        }

        $voidTags = [
            'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
            'link', 'meta', 'param', 'source', 'track', 'wbr',
        ];

        if (in_array($this->tag, $voidTags, true)) {
            return sprintf('<%s%s />', $this->tag, $attributes);
        }

        return sprintf('<%s%s>%s</%s>', $this->tag, $attributes, $this->content, $this->tag);
    }
}

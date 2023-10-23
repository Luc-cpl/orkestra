<?php

namespace Orkestra\Models;

class Post
{
	protected string $hash;

	public function __construct(
		protected int    $id,
		protected string $title,
		protected string $content,
	) {
		$this->hash = spl_object_hash($this);
	}
}
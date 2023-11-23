<?php

namespace Orkestra\Services\Http\Enum;

enum ParamType: string
{
	case String = 'string';
	case Int = 'int';
	case Number = 'number';
	case Boolean = 'boolean';
	case Array = 'array';
	case Object = 'object';
}

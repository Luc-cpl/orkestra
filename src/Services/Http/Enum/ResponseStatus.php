<?php

namespace Orkestra\Services\Http\Enum;

enum ResponseStatus: int
{
	case Ok = 200;
	case Created = 201;
	case Accepted = 202;
	case NoContent = 204;
	case BadRequest = 400;
	case Unauthorized = 401;
	case Forbidden = 403;
	case NotFound = 404;
	case MethodNotAllowed = 405;
	case Conflict = 409;
	case Gone = 410;
	case UnprocessableEntity = 422;
	case TooManyRequests = 429;
	case InternalServerError = 500;
	case NotImplemented = 501;
	case BadGateway = 502;
	case ServiceUnavailable = 503;
	case GatewayTimeout = 504;
	case HttpVersionNotSupported = 505;
	case VariantAlsoNegotiates = 506;
	case InsufficientStorage = 507;
	case LoopDetected = 508;
	case NotExtended = 510;
	case NetworkAuthenticationRequired = 511;
	case NetworkConnectTimeoutError = 599;
}

<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 27.02.2024 Time: 06:18
 */

namespace Router\Enums;

enum HttpStatus: int
{
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;
    case PARTIAL_CONTENT = 206;
    case MULTI_STATUS = 207;
    case ALREADY_REPORTED = 208;
    case IM_USED = 226;
    case MULTIPLE_CHOICES = 300;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case SEE_OTHER = 303;
    case NOT_MODIFIED = 304;
    case USE_PROXY = 305;
    case UNUSED = 306;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case GONE = 410;
    case LENGTH_REQUIRED = 411;
    case PRECONDITION_FAILED = 412;
    case PAYLOAD_TOO_LARGE = 413;
    case URI_TOO_LONG = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED = 417;
    case IM_A_TEAPOT = 418;
    case MISDIRECTED_REQUEST = 421;
    case UNPROCESSABLE_ENTITY = 422;
    case LOCKED = 423;
    case FAILED_DEPENDENCY = 424;
    case TOO_EARLY = 425;
    case UPGRADE_REQUIRED = 426;
    case PRECONDITION_REQUIRED = 428;
    case TOO_MANY_REQUESTS = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case VARIANT_ALSO_NEGOTIATES = 506;
    case INSUFFICIENT_STORAGE = 507;
    case LOOP_DETECTED = 508;
    case NOT_EXTENDED = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;
    case NETWORK_CONNECT_TIMEOUT_ERROR = 599;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return match ($this) {
            self::OK => "Success",
            self::CREATED => "Successfully created resource",
            self::ACCEPTED => "The request has been accepted for processing",
            self::NON_AUTHORITATIVE_INFORMATION => "The returned meta information is from a cached copy",
            self::NO_CONTENT => "The request has been successfully processed and the response is intentionally blank",
            self::RESET_CONTENT => "The request has been successfully processed and the user agent should reset the document view which caused the request to be sent",
            self::PARTIAL_CONTENT => "The server is delivering only part of the resource due to a range header sent by the client",
            self::MULTI_STATUS => "The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made",
            self::ALREADY_REPORTED => "The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again",
            self::IM_USED => "The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance",
            self::MULTIPLE_CHOICES => "The requested resource corresponds to any one of a set of representations, each with its own specific location",
            self::MOVED_PERMANENTLY => "The requested resource has been assigned a new permanent URI",
            self::FOUND => "The requested resource resides temporarily under a different URI",
            self::SEE_OTHER => "The response to the request can be found under a different URI",
            self::NOT_MODIFIED => "Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-Match",
            self::USE_PROXY => "The requested resource is available only through a proxy, whose address is provided in the response",
            self::UNUSED => "This code was used in a previous version of the HTTP specification",
            self::TEMPORARY_REDIRECT => "The request should be repeated with another URI",
            self::PERMANENT_REDIRECT => "The request and all future requests should be repeated using another URI",
            self::BAD_REQUEST => "The server cannot or will not process the request due to something that is perceived to be a client error",
            self::UNAUTHORIZED => "The request has not been applied because it lacks valid authentication credentials for the target resource",
            self::PAYMENT_REQUIRED => "Reserved for future use",
            self::FORBIDDEN => "You don't have permission to access this page",
            self::NOT_FOUND => "The requested resource could not be found",
            self::METHOD_NOT_ALLOWED => "The request method is not supported by the target resource",
            self::NOT_ACCEPTABLE => "The target resource does not have a current representation that would be acceptable to the user agent, according to the proactive negotiation header fields received in the request",
            self::PROXY_AUTHENTICATION_REQUIRED => "The client must authenticate itself to get the requested response",
            self::REQUEST_TIMEOUT => "The server did not receive a complete request message within the time that it was prepared to wait",
            self::CONFLICT => "The request could not be completed due to a conflict with the current state of the target resource",
            self::GONE => "The target resource is no longer available at the origin server and that this condition is likely to be permanent",
            self::LENGTH_REQUIRED => "The server refuses to accept the request without a defined Content-Length",
            self::PRECONDITION_FAILED => "One or more preconditions given in the request header fields evaluated to false when tested on the server",
            self::PAYLOAD_TOO_LARGE => "The server is refusing to process a request because the request payload is larger than the server is willing or able to process",
            self::URI_TOO_LONG => "The server is refusing to service the request because the request-target is longer than the server is willing to interpret",
            self::UNSUPPORTED_MEDIA_TYPE => "The server is refusing to service the request because the payload is in a format not supported by this method on the target resource",
            self::RANGE_NOT_SATISFIABLE => "The server cannot produce a response matching the list of ranges given in the request's Range header field",
            self::EXPECTATION_FAILED => "The server cannot meet the requirements of the Expect request-header field",
            self::IM_A_TEAPOT => "This code was defined in 1998 as one of the traditional IETF April Fools' jokes",
            self::MISDIRECTED_REQUEST => "The server is not able to produce a response for this request, though it understands the request",
            self::UNPROCESSABLE_ENTITY => "The server understands the content type of the request payload and the syntax of the payload is correct, but it was unable to process the contained instructions",
            self::LOCKED => "The resource that is being accessed is locked",
            self::FAILED_DEPENDENCY => "The method could not be performed on the resource because the requested action depended on another action and that action failed",
            self::TOO_EARLY => "The server is unwilling to risk processing a request that might be replayed",
            self::UPGRADE_REQUIRED => "The client should switch to a different protocol",
            self::PRECONDITION_REQUIRED => "The server requires the request to be conditional",
            self::TOO_MANY_REQUESTS => "The user has sent too many requests in a given amount of time",
            self::REQUEST_HEADER_FIELDS_TOO_LARGE => "The server is unwilling to process the request because its header fields are too large",
            self::UNAVAILABLE_FOR_LEGAL_REASONS => "The server cannot serve the requested content because it is legally restricted",
            self::INTERNAL_SERVER_ERROR => "The server encountered an internal error or misconfiguration and was unable to complete your request",
            self::NOT_IMPLEMENTED => "The server does not support the functionality required to fulfill the request",
            self::BAD_GATEWAY => "The server was acting as a gateway or proxy and received an invalid response from the upstream server",
            self::SERVICE_UNAVAILABLE => "The server is currently unable to handle the request due to a temporary overloading or maintenance of the server",
            self::GATEWAY_TIMEOUT => "The server was acting as a gateway or proxy and did not receive a timely response from the upstream server",
            self::HTTP_VERSION_NOT_SUPPORTED => "The server does not support the HTTP protocol version used in the request",
            self::VARIANT_ALSO_NEGOTIATES => "Transparent content negotiation for the request results in a circular reference",
            self::INSUFFICIENT_STORAGE => "The server is unable to store the representation needed to complete the request",
            self::LOOP_DETECTED => "The server detected an infinite loop while processing the request",
            self::NOT_EXTENDED => "Further extensions to the request are required for the server to fulfill it",
            self::NETWORK_AUTHENTICATION_REQUIRED => "The client needs to authenticate to gain network access",
            self::NETWORK_CONNECT_TIMEOUT_ERROR => "The connection to the network has timed out",
        };
    }
}

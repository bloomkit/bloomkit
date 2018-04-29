<?php

namespace Bloomkit\Core\Http;

final class HttpEvents
{
    const CONTROLLER = 'http.controller';

    const EXCEPTION = 'http.exception';

    const REQUEST = 'http.request';

    const RESPONSE = 'http.response';

    const TERMINATE = 'http.terminate';

    const VIEW = 'http.view';

    const FINISH_REQUEST = 'http.finish';
}

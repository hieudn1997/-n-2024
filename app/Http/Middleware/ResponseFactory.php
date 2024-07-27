<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ResponseFactory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $status = $response->status();

        if ($request->wantsJson() && $status !== 204) {
            $data = $response->getOriginalContent();
            $isSuccess = !isset($data['errors']) && !isset($data['exception']);
            $response->setContent(
                json_encode([
                    'data' => $isSuccess ? $data : (object)[],
                    'errors' =>  $data['errors'] ?? (object)[],
                    'message' => empty($data['message']) ? HttpFoundationResponse::$statusTexts[$status]: $data['message'],
                    'status' => $status,
                ]),
            );
        }

        return $response;
    }
}

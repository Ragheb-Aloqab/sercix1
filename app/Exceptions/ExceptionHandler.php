<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Global exception handler for HTTP errors.
 * Handles 401, 403, 404, 419, 500 with appropriate views or JSON for AJAX.
 * Used via renderable() in bootstrap/app.php.
 */
class ExceptionHandler
{
    /**
     * Determine if the request expects JSON (AJAX / API).
     */
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    /**
     * Build JSON error response for AJAX requests.
     */
    protected function jsonResponse(int $status, string $message, ?array $suggestions = null): Response
    {
        $payload = [
            'message' => $message,
            'status' => $status,
        ];
        if ($suggestions !== null) {
            $payload['suggestions'] = $suggestions;
        }
        return response()->json($payload, $status);
    }

    /**
     * Handle 401 Unauthorized - redirect to login with message.
     */
    protected function handle401(Request $request): Response
    {
        if ($this->wantsJson($request)) {
            return $this->jsonResponse(401, 'Please login to continue', [
                'Redirect to login page and try again.',
            ]);
        }
        return redirect()
            ->guest(route('sign-in.index'))
            ->with('error', __('errors.please_login'));
    }

    /**
     * Handle 403 Forbidden - access denied.
     */
    protected function handle403(Request $request): Response
    {
        if ($this->wantsJson($request)) {
            return $this->jsonResponse(403, 'Access denied.', [
                'You do not have permission to perform this action.',
            ]);
        }
        return response()->view('errors.403', [], 403);
    }

    /**
     * Handle 404 Not Found.
     */
    protected function handle404(Request $request): Response
    {
        if ($this->wantsJson($request)) {
            return $this->jsonResponse(404, 'Resource not found.', [
                'Check the URL or go back to the homepage.',
            ]);
        }
        return response()->view('errors.404', [], 404);
    }

    /**
     * Handle 419 Page Expired (CSRF token mismatch / session timeout).
     */
    protected function handle419(Request $request): Response
    {
        if ($this->wantsJson($request)) {
            return $this->jsonResponse(419, 'Page expired. Please refresh and try again.', [
                'Refresh the page and retry your action.',
                'If the problem persists, clear your browser cache.',
            ]);
        }
        return response()->view('errors.419', [], 419);
    }

    /**
     * Handle 500 Server Error.
     */
    protected function handle500(Request $request, ?string $message = null): Response
    {
        if ($this->wantsJson($request)) {
            return $this->jsonResponse(500, $message ?? 'Server error. Please try again later.', [
                'Refresh the page and try again.',
                'If the problem persists, contact support.',
            ]);
        }
        return response()->view('errors.500', ['message' => $message], 500);
    }

    /**
     * Log the exception (all errors are logged for debugging).
     */
    protected function logException(Throwable $e): void
    {
        Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Render exception into an HTTP response.
     * Returns null to let Laravel's default handler process the exception.
     */
    public function render($request, Throwable $e): ?Response
    {
        // Let Laravel handle validation errors (redirect back with errors)
        if ($e instanceof ValidationException) {
            return null;
        }

        // Always log for debugging (except validation - too noisy)
        $this->logException($e);

        // 401 - AuthenticationException (unauthenticated)
        if ($e instanceof AuthenticationException) {
            return $this->handle401($request);
        }

        // 403 - AuthorizationException / AccessDeniedHttpException
        if ($e instanceof AccessDeniedHttpException) {
            return $this->handle403($request);
        }
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->handle403($request);
        }

        // 419 - TokenMismatchException (CSRF / session expired)
        if ($e instanceof TokenMismatchException) {
            return $this->handle419($request);
        }

        // 404 - NotFoundHttpException
        if ($e instanceof NotFoundHttpException) {
            return $this->handle404($request);
        }

        // HttpException with specific status codes
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            return match ($status) {
                401 => $this->handle401($request),
                403 => $this->handle403($request),
                404 => $this->handle404($request),
                419 => $this->handle419($request),
                500 => $this->handle500($request, $e->getMessage() ?: null),
                default => $status >= 500
                    ? $this->handle500($request, $e->getMessage() ?: null)
                    : null,
            };
        }

        // Fallback: any unhandled exception -> 500
        return $this->handle500($request, __('errors.server_error_message'));
    }
}

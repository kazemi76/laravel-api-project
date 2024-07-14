<?php

namespace App\Exceptions;

use App\Http\Controllers\ApiController;
use Exception;
use Error;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use ErrorException;
use BadMethodCallException;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ApiController
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 404);
        }

        if ($e instanceof NotFoundHttpException) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

        if ($e instanceof Exception) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

        if ($e instanceof Error) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

        if ($e instanceof QueryException) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }

        // if (config('app.debug')) {
        //     DB::rollBack();
        //     return parent::render($request, $e);
        // }

        DB::rollBack();
        return $this->errorResponse($e->getMessage(), 500);
    }
}

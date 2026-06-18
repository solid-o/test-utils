<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** @internal */
final readonly class RethrowingExceptionHandler implements ExceptionHandler
{
    public function __construct(private ExceptionHandler $decorated)
    {
    }

    public function report(Throwable $e): void
    {
        $this->decorated->report($e);
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->decorated->shouldReport($e);
    }

    /**
     * @param Request $request
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function render($request, Throwable $e): Response
    {
        throw $e;
    }

    /**
     * @param OutputInterface $output
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function renderForConsole($output, Throwable $e): void
    {
        $this->decorated->renderForConsole($output, $e);
    }

    /** @param mixed[] $arguments */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->decorated->$name(...$arguments);
    }
}

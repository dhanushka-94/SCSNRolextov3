<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Run an existing web FormRequest against an API request so the same rules,
     * authorisation and prepare-logic are reused. The API user and route are passed
     * in directly because token-authenticated requests have no web session.
     *
     * @param  class-string<FormRequest>  $formClass
     */
    protected function formFrom(string $formClass, Request $request): FormRequest
    {
        /** @var FormRequest $form */
        $form = $formClass::createFrom($request);
        $form->setUserResolver(fn () => $request->user());
        $form->setRouteResolver(fn () => $request->route());
        $form->setContainer(app())->setRedirector(app('redirect'));
        $form->validateResolved();

        return $form;
    }

    /**
     * @param  class-string<FormRequest>  $formClass
     * @return array<string, mixed>
     */
    protected function validatedFrom(string $formClass, Request $request): array
    {
        return $this->formFrom($formClass, $request)->validated();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Dobrovolné přihlášení k newsletteru z podepsaného odkazu v e-mailu
 * (route newsletter.optin). Parametry nesou jen query string podepsané URL —
 * podpis ověřuje middleware `signed`, tady jen tvar hodnot.
 */
class NewsletterOptInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'source' => ['required', 'string', 'max:50', 'alpha_dash'],
        ];
    }

    /** Podepsaný odkaz s nevalidními daty nevznikne — místo redirectu zpět jen 422. */
    protected function failedValidation(Validator $validator): void
    {
        abort(422);
    }

    public function normalizedEmail(): string
    {
        return mb_strtolower(trim((string) $this->validated('email')));
    }

    public function source(): string
    {
        return (string) $this->validated('source');
    }
}

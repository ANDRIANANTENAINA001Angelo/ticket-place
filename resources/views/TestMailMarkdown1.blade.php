@component('mail::message')
# {{ $title }}

{{ $content }}

@component('mail::button', ['url' => config('app.frontend_url')])
Visitez {{ config('app.name') }}
@endcomponent

---

@component('mail::panel')
Merci d'utiliser **{{ config('app.name') }}** pour vos besoins. Nous espérons vous revoir bientôt !
@endcomponent

@component('mail::subcopy')
© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés. Si vous avez des questions, contactez-nous via [{{ config('app.email') }}](mailto:{{ config('app.email') }}).
@endcomponent
@endcomponent

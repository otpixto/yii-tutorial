@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
# Здравствуйте!
@endif

{{-- Intro Lines --}}
@isset($introLines)
@foreach ($introLines as $line)
{{ $line }}

@endforeach
@endisset

{{-- Action Button --}}
@isset($actionText)
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@isset($outroLines)
@foreach ($outroLines as $line)
{{ $line }}

@endforeach
@endisset

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
С наилучшими пожеланиями,<br>{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
Если у вас возникли проблемы с нажатием кнопки "{{ $actionText }}", Скопируйте и вставьте URL-адрес ниже
В ваш веб-браузер: [{{ $actionUrl }}]({{ $actionUrl }})
@endcomponent
@endisset
@endcomponent

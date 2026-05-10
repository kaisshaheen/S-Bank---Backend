@component('mail::message')
# Monthly Bank Statement

Hello 👋,

Here is your monthly statement.

@component('mail::panel')
**Account Number:** {{ $account['account_number'] }}  
**Account Type:** {{ ucfirst($account['type']) }}  
**Current Balance:** {{ number_format($account['balance'],2) }}
@endcomponent

The detailed statement is attached as a PDF.

Thanks for banking with us,  
**{{ config('app.name') }}**
@endcomponent
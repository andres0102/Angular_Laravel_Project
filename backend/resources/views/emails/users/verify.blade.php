@component('mail::message')
<!-- # Congratulations -->
<span class="email_greetings">Dear {{ $data['name'] }},</span><br>
You have received this email because your user profile has been created in our database. All we need to do now is to make sure this is your email address.

Please click on the link below to verify your email and get started!

@component('mail::button', ['url' => $data['verify_url']])
Activate Account
@endcomponent

If you are unable to view the button above, here is an alternative link for you to copy and paste into your browser:

{{ $data['verify_url'] }}

Should you have any concerns, enquiries or require assistance, please do not hesitate to contact our dedicated IT helpdesk at <a href="tel:{{$data['support_no']}}">{{$data['support_no']}}</a>, or simply reply to this email and they will be with you shortly.

<span class="email_regards">Warmest Regards,</span><br>
<span class="email_name">Technical Support</span><br>
<span class="email_job">Legacy FA Pte Ltd</span>
@endcomponent

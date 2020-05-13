@component('mail::message')
<!-- # Congratulations -->
<span class="email_greetings">Dear {{ $data['name'] }},</span><br>
You have received this email because there was a request made to reset your password on {{ $data['request_date'] }} at {{ $data['request_time'] }}.

Please click on the link below to reset your password:

@component('mail::button', ['url' => $data['reset_url']])
Reset Password
@endcomponent

Please note that this link will expire automatically after 30 minutes.

If you are unable to view the button above, here is an alternative link for you to copy and paste into your browser:

{{ $data['reset_url'] }}

If are certain that you did not request to reset your password, it is of utmost importance that you inform us immediately and our department will investigate on this malicious attempt promptly. As a best practice for digital security, please do not reveal your credentials to anyone and do make it a habit to change them every 6 month(s).

Should you have any concerns, enquiries or require assistance, please do not hesitate to contact our dedicated IT helpdesk at <a href="tel:{{$data['support_no']}}">{{$data['support_no']}}</a>, or simply reply to this email and they will be with you shortly.

<span class="email_regards">Warmest Regards,</span><br>
<span class="email_name">Technical Support</span><br>
<span class="email_job">Legacy FA Pte Ltd</span>
@endcomponent

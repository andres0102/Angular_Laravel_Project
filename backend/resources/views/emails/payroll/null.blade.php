@component('mail::message')
<?php
$months = array(
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July ',
  'August',
  'September',
  'October',
  'November',
  'December',
);
?>
<!-- # Congratulations -->
<span class="email_greetings">Dear {{$name}},</span><br>
It appears that we do not have any payroll data for you in {{$months[(int)$month-1]}} {{$year}}.

Please do not hesitate to inform us if you have any issues.

<span class="email_regards">Warmest Regards,</span><br>
<span class="email_name">Finance Department</span><br>
<span class="email_job">Legacy FA Pte Ltd</span>
@endcomponent

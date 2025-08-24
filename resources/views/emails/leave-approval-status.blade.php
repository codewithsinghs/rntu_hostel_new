{{-- resources/views/emails/leave-approval-status.blade.php --}}
@component('mail::message')
# Your Leave Request Status

Hello {{ $studentName }},

Your leave request for the period **{{ $leaveStartDate }}** to **{{ $leaveEndDate }}** (Reason: {{ $reason }}) has been **{{ ucfirst($status) }}**.

@if ($status == 'approved')
We wish you a safe journey and look forward to your return.
@else
Please contact the hostel administration for further details or clarification.
@endif

Thanks,<br>
{{ $hostelName }} Team
@endcomponent
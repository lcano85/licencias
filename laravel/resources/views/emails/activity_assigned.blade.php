@component('mail::message')
# New Activity Assigned

Hello,  

You have been assigned to a new activity: **{{ $activity->activity_name }}**  

**Description:**  
{{ $activity->main_description }}

@component('mail::button', ['url' => route('activity.view', $activity->id)])
View Activity
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
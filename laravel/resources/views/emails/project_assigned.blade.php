@component('mail::message')
# New Project Assigned

Hello,  

You have been assigned to a new project: **{{ $project->project_title }}**  

**Description:**  
{{ $project->description }}

Thanks,  
{{ config('app.name') }}
@endcomponent
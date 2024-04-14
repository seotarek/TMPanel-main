<div class="flex flex-col justify-center items-center h-screen text-center">

    <div class="flex flex-col justify-center items-center text-center">
        <img src="{{asset('images/TM-logo.svg')}}" class="h-12 mb-4" alt="TMPanel Logo">
      {{--  <span class="text-[0.9rem] font-medium text-gray-950 dark:text-white">
            Welcome to the TMPanel installer. Please fill out the form below to get started.
        </span>--}}
    </div>

    <div class="w-[50rem] mt-8">
        {{$this->form}}
    </div>

</div>

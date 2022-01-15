<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\UpdateMission;
use App\Event;
use App\Mission;

class SendUpdateMissionNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UpdateMission $event)
    {
        $missions = Mission::find($event->mission_ids ?? []);

        $gateways = [];
        if(env('MAIL_USERNAME') == null && env('MAIL_PASSWORD') == null && env('MAIL_DRIVER') != 'sendmail'){
            $gateways[] = 'mail';
        }
        if(\App\Models\BusinessSetting::whereType('nexmo')->first()->value ?? 0 == 1 || \App\Models\BusinessSetting::whereType('ebernate')->first()->value ?? 0 == 1 || \App\Models\BusinessSetting::whereType('twillo')->first()->value ?? 0 == 1  || \App\Models\BusinessSetting::whereType('ssl_wireless')->first()->value ?? 0 == 1   || \App\Models\BusinessSetting::whereType('fast2sms')->first()->value ?? 0 == 1 || \App\Models\BusinessSetting::whereType('mimo')->first()->value ?? 0 == 1){
            $gateways[] = 'sms';
        }
        $gateways[] = 'database';

        $notify = json_decode(\App\BusinessSetting::where('type', 'notifications')->where('key','mission_action')->first()->value, true);

        $users  =   [];
        if(isset($notify['administrators'])){
            $users  =   array_merge($users, $notify['administrators']);
        }
        if(isset($notify['roles'])){
            $roles_users    =   \App\User::where('user_type', 'staff')->whereIn('role_id',$notify['roles'])->pluck('id')->toArray();
            $users          =   array_merge($users, $roles_users);
        }
        if(isset($notify['employees'])){
            $users  =   array_merge($users, $notify['employees']);
        }

        foreach ($missions as $mission)
        { 
            $action = Mission::getStatusByStatusId($mission->status_id);

            if(isset($notify['sender'])){
                $users  =   array_merge($users, array($mission->client->userClient->user_id));
            }
            if(isset($notify['captain'])){
                $users  =   array_merge($users, array($mission->captain->userCaptain->user_id));
            }

            $title      = translate('There is an updated mission');
            $content    = translate('Please check the mission which is just updated right now!');
            $url        = url('admin/missions').'/'.$mission->id;

            foreach($users as $user){
                $available_gateways = $gateways;
                $recevier   =   \App\User::find($user);
                if($recevier){
                    if($recevier->phone == null){
                        if (($key = array_search('sms', $available_gateways)) !== false) {
                            unset($available_gateways[$key]);
                        }
                    }
                    if($recevier->email == null){
                        if (($key = array_search('email', $available_gateways)) !== false) {
                            unset($available_gateways[$key]);
                        }
                    }

                    $data = array(
                        'sender'    =>  \Auth::user(),
                        'message'   =>  array(
                                'subject'   =>  $title,
                                'content'   =>  $content,
                                'url'       =>  $url,
                                'id'        =>  $mission->id,
                                'code'      =>  $mission->code,
                        ),
                        'icon'      =>  'flaticon2-bell-4',
                        'type'      =>  'mission_action',
                    );
                    $recevier->notify(new \App\Notifications\GlobalNotification($data, $available_gateways));

                }
            }
        }
    }
}

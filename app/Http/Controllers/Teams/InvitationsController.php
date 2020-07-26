<?php

namespace App\Http\Controllers\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Mail\SendInvitationToJoinTeam;
use App\Repositories\Contracts\IInvitation;

class InvitationsController extends Controller
{
    
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(
        IInvitation $invitations,
        ITeam $teams,
        IUser $users
    )
    {
        $this->invitations = $invitations;
        $this->teams = $teams;
        $this->users = $users;
    }

    public function invite(Request $request, $teamId)
    {
        // Get the team
        $team = $this->teams->find($teamId);

        $this->validate($request, [
            'email' => ['required', 'email']
        ]);

        $user = auth()->user();

        // Check if the user owns the team
        if(! $user->isOwnerOfTeam($team)){
            return response()->json([
                'email' => 'You are not the team owner'
            ], 401);
        }

        // Check if the email has pending invitation
        if($team->hasPendingInvite($request->email)){
            return response()->json([
                'email' => 'Email has already a pending invite'
            ], 422);
        }

        // Get the recipient by email
        $recipient = $this->users->findByEmail($request->email);

        // If the recipient does not exist, send an invitation to join the team
        if(! $recipient){
            $this->createInvitation(false, $team, $request->email);            
            return response()->json([
                'message' => 'Invitation sent to user'
            ], 200);
        }

        // Check if the team has the user
        if($team->hasUser($recipient)){
            return response()->json([
                'email' => 'This user seems to be a team member already'
            ], 422);
        }

        // Send the invitation to the user
        $this->createInvitation(true, $team, $request->email);
        return response()->json([
            'message' => 'Invitation sent to user'
        ], 200);
    }

    public function resend($id)
    {
        $invitation = $this->invitations->find($id);

        $this->authorize('resend', $invitation);
        
        $recipient = $this->users
                            ->findByEmail($invitation->recipient_email);
        
        Mail::to($invitation->recipient_email)
            ->send(new SendInvitationToJoinTeam($invitation, !is_null($recipient)));
        
        return response()->json(['message' => 'Invitation resent'], 200);
    }
  
    public function respond(Request $request, $id)
    {
        $this->validate($request, [
            'token' => ['required'],
            'decision' => ['required'],
        ]);

        $token = $request->token;
        $decision = $request->decision;  // 'accept' or 'deny'
        $invitation = $this->invitations->find($id);

        // Check if the invitation belongs to user
        $this->authorize('respond', $invitation);

        // Check to make sure that the token match
        if($invitation->token !== $token){
            return response()->json([
                'message' => 'Invalid token'
            ], 401);
        }

        // Check if accepted
        if($decision !== 'deny'){
            $this->invitations->addUserToTeam($invitation->team, auth()->id());
        }

        $invitation->delete();

        return response()->json([
            'message' => 'Successful'
        ], 200);
    }

    public function destroy($id)
    {
        $invitation = $this->invitations->find($id);
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }

    protected function createInvitation(bool $user_exists, Team $team, string $email)
    {
        $invitation = $this->invitations->create([
            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime()))
        ]);
        Mail::to($email)
            ->send(new SendInvitationToJoinTeam($invitation, $user_exists));
    }
}

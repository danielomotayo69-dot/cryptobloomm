<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Settings;
use App\Models\Logo;
use App\Models\Currency;
use App\Models\Transfer;
use App\Models\Plans;
use App\Models\Gateway;
use App\Models\Deposits;
use App\Models\Withdraw;
use App\Models\Profits;
use App\Models\Social;
use App\Models\About;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\Reply;
use App\Models\Review;
use App\Models\Earning;
use App\Models\Referral;
use Carbon\Carbon;
use Image;
use App\Mail\UserNotification;

class AdminController extends Controller
{
    public function __construct()
    {		
        $this->middleware('auth');
    }

    public function Destroyuser($id)
    {
        User::whereId($id)->delete();
        Profits::whereUser_id($id)->delete();
        Deposits::whereUser_id($id)->delete();
        Ticket::whereUser_id($id)->delete();
        Withdraw::whereUser_id($id)->delete();
        Earning::whereReferral($id)->delete();
        Referral::whereRef_id($id)->delete();
        Transfer::where('sender_id',$id)->orWhere('receiver_id',$id)->delete();

        return back()->with('success', 'User was successfully deleted');
    } 
        
    public function dashboard()
    {
        $data['title'] = 'Dashboard';
        $data['totalusers'] = User::count();
        $data['blockedusers'] = User::whereStatus(1)->count();
        $data['activeusers'] = User::whereStatus(0)->count();
        $data['totalticket'] = Ticket::count();
        $data['openticket'] = Ticket::whereStatus(0)->count();
        $data['closedticket'] = Ticket::whereStatus(1)->count();        
        $data['totalreview'] = Review::count();
        $data['pubreview'] = Review::whereStatus(1)->count();
        $data['unpubreview'] = Review::whereStatus(0)->count();        
        $data['totaldeposit'] = Deposits::count();
        $data['approveddep'] = Deposits::whereStatus(1)->count();
        $data['declineddep'] = Deposits::whereStatus(2)->count();
        $data['pendingdep'] = Deposits::whereStatus(0)->count();               
        $data['totalwd'] = Withdraw::count();
        $data['approvedwd'] = Withdraw::whereStatus(1)->count();
        $data['declinedwd'] = Withdraw::whereStatus(2)->count();
        $data['pendingwd'] = Withdraw::whereStatus(0)->count();        
        $data['totalplan'] = Plans::count();
        $data['appplan'] = Plans::whereStatus(1)->count();
        $data['penplan'] = Plans::whereStatus(0)->count();        
        $data['totalprofit'] = Profits::count();
        $data['appprofit'] = Profits::whereStatus(1)->count();
        $data['penprofit'] = Profits::whereStatus(0)->count();
        $data['messages'] = Contact::count();
        return view('admin.dashboard.index', $data);
    }    
    
    public function Users()
    {
        $data['title'] = 'Clients';
        $data['users'] = User::latest()->get();
        return view('admin.user.index', $data);
    }    
    
    public function Messages()
    {
        $data['title'] = 'Messages';
        $data['message'] = Contact::latest()->get();
        return view('admin.user.message', $data);
    }    
    
    public function Ticket()
    {
        $data['title'] = 'Ticket system';
        $data['ticket'] = Ticket::latest()->get();
        return view('admin.user.ticket', $data);
    }   
    
    public function Email($id, $name)
    {
        $data['title'] = 'Send email';
        $data['email'] = $id;
        $data['name'] = $name;
        return view('admin.user.email', $data);
    }    
    
    public function Promo()
    {
        $data['title'] = 'Send email';
        $data['client'] = User::all();
        return view('admin.user.promo', $data);
    } 
    
    // ------------------------- EMAIL METHODS -------------------------
    public function Sendemail(Request $request)
    {        	
        $data = [
            'name' => $request->name,
            'message' => $request->message,
        ];

        Mail::to($request->to)->send(new UserNotification($data));

        $notification = [
            'message' => 'Mail Sent Successfully!',
            'alert-type' => 'success'
        ];

        return back()->with($notification);
    }
    
    public function Sendpromo(Request $request)
    {        	
        foreach ($request->email as $email) {
            $user = User::whereEmail($email)->first();
            if ($user) {
                $data = [
                    'name' => $user->name,
                    'message' => $request->message,
                ];
                Mail::to($user->email)->send(new UserNotification($data));
            }
        }      

        $notification = [
            'message' => 'Promo Mails Sent Successfully!',
            'alert-type' => 'success'
        ];

        return back()->with($notification);
    }
    // -------------------------------------------------------------------

    public function Replyticket(Request $request)
    {        
        $data['ticket_id'] = $request->ticket_id;
        $data['reply'] = $request->reply;
        $data['status'] = 0;
        $res = Reply::create($data);      
        if ($res) {
            return back();
        } else {
            return back()->with('alert', 'An error occurred');
        }
    }    
    
    public function Destroymessage($id)
    {
        $data = Contact::findOrFail($id);
        $res = $data->delete();
        if ($res) {
            return back()->with('success', 'Request was Successfully deleted!');
        } else {
            return back()->with('alert', 'Problem With Deleting Request');
        }
    }     
    
    public function Destroyticket($id)
    {
        $data = Ticket::findOrFail($id);
        $res = $data->delete();
        if ($res) {
            return back()->with('success', 'Request was Successfully deleted!');
        } else {
            return back()->with('alert', 'Problem With Deleting Request');
        }
    } 

    public function Manageuser($id)
    {
        $user = User::find($id);
        $data['client'] = $user;
        $data['title'] = $user->name;
        $data['deposit'] = Deposits::whereUser_id($user->id)->orderBy('id', 'DESC')->get();
        $data['withdraw'] = Withdraw::whereUser_id($user->id)->orderBy('id', 'DESC')->get();
        $data['profit'] = Profits::whereUser_id($user->id)->orderBy('id', 'DESC')->get();
        $data['ticket'] = Ticket::whereUser_id($user->id)->orderBy('id', 'DESC')->get();
        $data['earning'] = Earning::whereReferral($user->id)->orderBy('id', 'DESC')->get();
        $data['referral'] = Referral::whereRef_id($user->id)->orderBy('id', 'DESC')->get();
        $data['transfer'] = Transfer::where('sender_id', $user->id)
                                    ->orWhere('receiver_id', $user->id)
                                    ->orderBy('id', 'DESC')->get();
        return view('admin.user.edit', $data);
    }     
    
    public function Manageticket($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            \Log::error("Ticket not found: ID ".$id);
            return redirect()->back()->with('error', 'Ticket not found.');
        }

        $data['ticket'] = $ticket;
        $data['title'] = '#' . $ticket->ticket_id;
        $data['client'] = User::find($ticket->user_id);
        $data['reply'] = Reply::where('ticket_id', $ticket->ticket_id)->get();

        return view('admin.user.edit-ticket', $data);
    }
    
    public function Closeticket($id)
    {
        $ticket = Ticket::find($id);
        $ticket->status = 1;
        $ticket->save();
        return back()->with('success', 'Ticket has been closed.');
    }     
    
    public function Blockuser($id)
    {
        $user = User::find($id);
        $user->status = 1;
        $user->save();
        return back()->with('success', 'User has been suspended.');
    } 

    public function Unblockuser($id)
    {
        $user = User::find($id);
        $user->status = 0;
        $user->save();
        return back()->with('success', 'User was successfully unblocked.');
    }

    public function Approvekyc($id)
    {
        $user = User::find($id);
        $user->kyc_status = 1;
        $user->save();
        return back()->with('success', 'Kyc has been approved.');
    }    
    
    public function Rejectkyc($id)
    {
        $user = User::find($id);
        $user->kyc_status = '';
        $user->save();
        return back()->with('success', 'Kyc was successfully rejected.');
    }

    public function Profileupdate(Request $request)
    {
        $data = User::findOrFail($request->id);
        $data->username = $request->username;
        $data->name = $request->name;
        $data->phone = $request->mobile;
        $data->country = $request->country;
        $data->city = $request->city;
        $data->zip_code = $request->zip_code;
        $data->address = $request->address;
        $data->balance = $request->balance;
        $data->email_verify = empty($request->email_verify) ? 0 : $request->email_verify;
        $data->phone_verify = empty($request->phone_verify) ? 0 : $request->phone_verify;
        $data->upgrade = empty($request->upgrade) ? 0 : $request->upgrade;

        $res = $data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occurred');
        }
    }
    
    public function logout()
    {
        Auth::guard()->logout();
        session()->flash('message', 'Just Logged Out!');
        return redirect('/admin');
    }
}
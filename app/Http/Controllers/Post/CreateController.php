<?php
/**
 * LaraClassified - Geo Classified Ads CMS
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Post;

use App\Helpers\Ip;
use App\Http\Controllers\Auth\Traits\VerificationTrait;
use App\Http\Controllers\Post\Traits\CustomFieldTrait;
use App\Http\Requests\PostRequest;
use App\Models\Permission;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Category;
use App\Models\Package;
use App\Models\City;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Http\Controllers\FrontController;
use App\Models\Scopes\ReviewedScope;
use App\Mail\PostNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use App\Http\Controllers\Post\Traits\EditTrait;

class CreateController extends FrontController
{
	use EditTrait, VerificationTrait, CustomFieldTrait;
	
	public $data;
	
	/**
	 * CreateController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Check if guests can post Ads
		if (config('settings.single.guests_can_post_ads') != '1') {
			$this->middleware('auth')->only(['getForm', 'postForm']);
		}
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// References
		$data = [];
		
		// Get Countries
		$data['countries'] = CountryLocalizationHelper::transAll(CountryLocalization::getCountries());
		view()->share('countries', $data['countries']);
		
		// Get Categories
		$cacheId = 'categories.parentId.0.with.children' . config('app.locale');
		$data['categories'] = Cache::remember($cacheId, $this->cacheExpiration, function () {
			$categories = Category::trans()->where('parent_id', 0)->with([
				'children' => function ($query) {
					$query->trans();
				},
			])->orderBy('lft')->get();
			return $categories;
		});
		view()->share('categories', $data['categories']);
		
		// Get Post Types
		$cacheId = 'postTypes.all.' . config('app.locale');
		$data['postTypes'] = Cache::remember($cacheId, $this->cacheExpiration, function () {
			$postTypes = PostType::trans()->orderBy('lft')->get();
			return $postTypes;
		});
		view()->share('postTypes', $data['postTypes']);
		
		// Count Packages
		$data['countPackages'] = Package::trans()->applyCurrency()->count();
		view()->share('countPackages', $data['countPackages']);
		
		// Count Payment Methods
		$data['countPaymentMethods'] = $this->countPaymentMethods;
		
		// Save common's data
		$this->data = $data;
	}
	
	/**
	 * New Post's Form.
	 *
	 * @param null $tmpToken
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function getForm($tmpToken = null)
	{
		// Check possible Update
		if (!empty($tmpToken)) {
			session()->keep(['message']);
			
			return $this->getUpdateForm($tmpToken);
		}		
		// Meta Tags
		MetaTag::set('title', getMetaTag('title', 'create'));
		MetaTag::set('description', strip_tags(getMetaTag('description', 'create')));
		MetaTag::set('keywords', getMetaTag('keywords', 'create'));
		$totalfreeads = DB::table('settings')->where('key', 'freeads')->first();
	    $id = \Auth::user()['id'];
	   
		if(isset($totalfreeads->value)){
		 $totalfreeads = $totalfreeads->value;
		
		 $totalposts = DB::table('posts')->where('user_id',$id)->whereRaw('DATE_ADD(`created_at`,INTERVAL 1 DAY)>now()')->count();
		 
		 if($totalposts<$totalfreeads){
			
		 }else {
			return view('post.posterror');
		 }

		
		}
		return view('post.create');
	}
	
	
	
	
		public function banneroption()
	{
		// Check possible Update
		if (!empty($tmpToken)) {
			session()->keep(['message']);
			
			return $this->getUpdateForm($tmpToken);
		}		
		// Meta Tags
		MetaTag::set('title', getMetaTag('title', 'create'));
		MetaTag::set('description', strip_tags(getMetaTag('description', 'create')));
		MetaTag::set('keywords', getMetaTag('keywords', 'create'));
		$totalfreeads = DB::table('settings')->where('key', 'freeads')->first();
	    $id = \Auth::user()['id'];
	   
		
		return view('post.bannerposts');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Store a new Post.
	 *
	 * @param null $tmpToken
	 * @param PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function postForm($tmpToken = null, PostRequest $request)
	{
		// Check possible Update
		if (!empty($tmpToken)) {
			session()->keep(['message']);
			
			return $this->postUpdateForm($tmpToken, $request);
		}
		
		// Get the Post's City
		$city = City::find($request->input('city_id', 0));
		// if (empty($city)) {
		// 	flash(t("Posting Ads was disabled for this time. Please try later. Thank you."))->error();
			
		// 	return back()->withInput();
		// }
		
		// Conditions to Verify User's Email or Phone
		if (auth()->check()) {
			$emailVerificationRequired = config('settings.mail.email_verification') == 1 && $request->filled('email') && $request->input('email') != auth()->user()->email;
			$phoneVerificationRequired = config('settings.sms.phone_verification') == 1 && $request->filled('phone') && $request->input('phone') != auth()->user()->phone;
		} else {
			$emailVerificationRequired = config('settings.mail.email_verification') == 1 && $request->filled('email');
			$phoneVerificationRequired = config('settings.sms.phone_verification') == 1 && $request->filled('phone');
		}
		
		// New Post
		$post = new Post();
		$input = $request->only($post->getFillable());
		foreach ($input as $key => $value) {
			$post->{$key} = $value;
		}
		
		$post->country_code = config('country.code');
		$post->user_id = (auth()->check()) ? auth()->user()->id : 0;
		$post->negotiable = $request->input('negotiable');
		$post->phone_hidden = $request->input('phone_hidden');
		$post->lat = 'sdsdsdsds';
		$post->lon = 'sdsdsdsdsds';
		$post->ip_addr = Ip::get();
		$post->tmp_token = md5(microtime() . mt_rand(100000, 999999));
		$post->verified_email = 1;
		$post->verified_phone = 1;
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$post->email_token = md5(microtime() . mt_rand());
			$post->verified_email = 0;
		}
		
		// Mobile activation key generation
		if ($phoneVerificationRequired) {
			$post->phone_token = mt_rand(100000, 999999);
			$post->verified_phone = 0;
		}
		
		// Save
		$post->save();
		//dd($post);
		// Save ad Id in session (for next steps)
		session(['tmpPostId' => $post->id]);
		
		// Custom Fields
		$this->createPostFieldsValues($post, $request);
		
		// The Post's creation message
		if (getSegment(2) == 'create') {
			session()->flash('message', t('Your ad has been created.'));
		}
		
		// Get Next URL
		$nextStepUrl = config('app.locale') . '/posts/create/' . $post->tmp_token . '/photos';
		
		// Send Admin Notification Email
		if (config('settings.mail.admin_email_notification') == 1) {
			try {
				// Get all admin users
				$admins = User::permission(Permission::getStaffPermissions())->get();
				if ($admins->count() > 0) {
					foreach ($admins as $admin) {
						Mail::send(new PostNotification($post, $admin));
					}
				}
			} catch (\Exception $e) {
				flash($e->getMessage())->error();
			}
		}
		
		// Send Email Verification message
		if ($emailVerificationRequired) {
			// Save the Next URL before verification
			session(['itemNextUrl' => $nextStepUrl]);
			
			// Send
			$this->sendVerificationEmail($post);
			
			// Show the Re-send link
			$this->showReSendVerificationEmailLink($post, 'post');
		}
		
		// Send Phone Verification message
		if ($phoneVerificationRequired) {
			// Save the Next URL before verification
			session(['itemNextUrl' => $nextStepUrl]);
			
			// Send
			$this->sendVerificationSms($post);
			
			// Show the Re-send link
			$this->showReSendVerificationSmsLink($post, 'post');
			
			// Go to Phone Number verification
			$nextStepUrl = config('app.locale') . '/verify/post/phone/';
		}
		
		// Redirection
		return redirect($nextStepUrl);
	}
	
	/**
	 * Confirmation
	 *
	 * @param $tmpToken
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function finish($tmpToken)
	{
		// Keep Success Message for the page refreshing
		session()->keep(['message']);
		if (!session()->has('message')) {
			return redirect(config('app.locale') . '/');
		}
		
		// Clear the steps wizard
		if (session()->has('tmpPostId')) {
			// Get the Post
			$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])->where('id', session('tmpPostId'))->where('tmp_token', $tmpToken)->first();
			if (empty($post)) {
				abort(404);
			}
			$user_id = $post->user_id;
			$user = DB::table('users')->where('id', $user_id)->first();
			$email = $user->email;
			$name = $user->name;
			$sam = array('name'=>$name, 'email'=>$email);
			if($post->price){
			      Mail::send(['html'=>'post_created'], $sam, function($message) {
			         $message->to($sam['email'], $sam['name'])->subject
			            ('Post created');
			         $message->from('sameekshasingh28199@gmail.com','ClassifiedZoo');
			      });
			}else{
				 Mail::send(['html'=>'post_created_pro'], $sam, function($message) {
			         $message->to($sam['email'], $sam['name'])->subject
			            ('Post created');
			         $message->from('sameekshasingh28199@gmail.com','ClassifiedZoo');
			      });
			}

			// Apply finish actions
			$post->tmp_token = null;
			$post->save();
			session()->forget('tmpPostId');
		}
		
		// Redirect to the Post,
		// - If User is logged
		// - Or if Email and Phone verification option is not activated
		if (auth()->check() || (config('settings.mail.email_verification') != 1 && config('settings.sms.phone_verification') != 1)) {
			if (!empty($post)) {
				flash(session('message'))->success();
				
				return redirect(config('app.locale') . '/' . $post->uri . '?preview=1');
			}
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('post.finish');
	}
}

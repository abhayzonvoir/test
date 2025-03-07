{{--
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
--}}
@extends('layouts.master')

<?php
// Phone
$phone = TextToImage::make($post->phone, IMAGETYPE_PNG, ['backgroundColor' => 'rgba(0,0,0,0.0)', 'color' => '#FFFFFF']);
$phoneLink = 'tel:' . $post->phone;
$phoneLinkAttr = '';
if (!auth()->check()) {
	if (config('settings.single.guests_can_contact_seller') != '1') {
		$phone = t('Click to see');
		$phoneLink = '#quickLogin';
		$phoneLinkAttr = 'data-toggle="modal"';
	}
}

// Contact Seller URL
$contactSellerURL = '#contactUser';
if (!auth()->check()) {
	if (config('settings.single.guests_can_contact_seller') != '1') {
		$contactSellerURL = '#quickLogin';
	}
}
?>

@section('content')
	{!! csrf_field() !!}
	<input type="hidden" id="post_id" value="{{ $post->id }}">
	
	@if (Session::has('flash_notification'))
		@include('common.spacer')
		<?php $paddingTopExists = true; ?>
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					@include('flash::message')
				</div>
			</div>
		</div>
		<?php Session::forget('flash_notification.message'); ?>
	@endif
	
	<div class="main-container">
		
		<?php if (\App\Models\Advertising::where('slug', 'top')->count() > 0): ?>
			@include('layouts.inc.advertising.top', ['paddingTopExists' => (isset($paddingTopExists)) ? $paddingTopExists : false])
		<?php
			$paddingTopExists = false;
		endif;
		?>
		@include('common.spacer')

		<div class="container">
			<ol class="breadcrumb pull-left">
				<li><a href="{{ lurl('/') }}"><i class="icon-home fa"></i></a></li>
				<li><a href="{{ lurl('/') }}">{{ config('country.name') }}</a></li>
				@if (!empty($post->category->parent))
					<li>
						<?php $attr = ['countryCode' => config('country.icode'), 'catSlug' => $post->category->parent->slug]; ?>
						<a href="{{ lurl(trans('routes.v-search-cat', $attr), $attr) }}">
							{{ $post->category->parent->name }}
						</a>
					</li>
					@if ($post->category->parent->id != $post->category->id)
					<li>
						<?php $attr = ['countryCode' => config('country.icode'), 'catSlug' => $post->category->parent->slug, 'subCatSlug' => $post->category->slug]; ?>
						<a href="{{ lurl(trans('routes.v-search-subCat', $attr), $attr) }}">
							{{ $post->category->name }}
						</a>
					</li>
					@endif
				@else
					<li>
						<?php $attr = ['countryCode' => config('country.icode'), 'catSlug' => $post->category->slug]; ?>
						<a href="{{ lurl(trans('routes.v-search-cat', $attr), $attr) }}">
							{{ $post->category->name }}
						</a>
					</li>
				@endif
				<li class="active">{{ str_limit($post->title, 70) }}</li>
			</ol>
			<div class="pull-right backtolist">
                <a href="{{ URL::previous() }}">
                    <i class="fa fa-angle-double-left"></i> {{ t('Back to Results') }}
                </a>
            </div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-sm-9 page-content col-thin-right">
					<div class="inner inner-box ads-details-wrapper">
						<h2 class="enable-long-words">
							<strong>
								<?php $attr = ['slug' => slugify($post->title), 'id' => $post->id]; ?>
								<a href="{{ lurl($post->uri, $attr) }}" title="{{ $post->title }}">
									{{ $post->title }}
                                </a>
                            </strong>
							<small class="label label-default adlistingtype">{{ $post->postType->name }}</small>
							@if ($post->featured==1 and !empty($post->latestPayment))
								@if (isset($post->latestPayment->package) and !empty($post->latestPayment->package))
									<i class="icon-ok-circled tooltipHere" style="color: {{ $post->latestPayment->package->ribbon }};" title="" data-placement="right"
									   data-toggle="tooltip" data-original-title="{{ $post->latestPayment->package->short_name }}"></i>
								@endif
                            @endif
						</h2>
						<span class="info-row">
							<span class="date"><i class=" icon-clock"> </i> {{ $post->created_at_ta }} </span> -&nbsp;
							<span class="category">{{ (!empty($post->category->parent)) ? $post->category->parent->name : $post->category->name }}</span> -&nbsp;
							<span class="item-location"><i class="fa fa-map-marker"></i> {{ $post->city->name }} </span> -&nbsp;
							<span class="category">
								<i class="icon-eye-3"></i>&nbsp;
								{{ \App\Helpers\Number::short($post->visits) }} {{ trans_choice('global.count_views', getPlural($post->visits)) }}
							</span>
						</span>
						
						<div class="ads-image">
							@if (!in_array($post->category->type, ['non-salable']))
								<h1 class="pricetag">
									@if ($post->price > 0)
										{!! \App\Helpers\Number::money($post->price) !!}
									@else
										{!! \App\Helpers\Number::money(' --') !!}
									@endif
								</h1>
							@endif
							@if (count($post->pictures) > 0)
								<ul class="bxslider">
									@foreach($post->pictures as $key => $image)
										<li><img src="{{ resize($image->filename, 'big') }}" alt="img"></li>
									@endforeach
								</ul>
								<div class="product-view-thumb-wrapper">
									<ul id="bx-pager" class="product-view-thumb">
									@foreach($post->pictures as $key => $image)
										<li>
											<a class="thumb-item-link" data-slide-index="{{ $key }}" href="">
												<img src="{{ resize($image->filename, 'small') }}" alt="img">
											</a>
										</li>
									@endforeach
									</ul>
								</div>
							@else
								<ul class="bxslider">
									<li><img src="{{ resize(config('larapen.core.picture.default'), 'big') }}" alt="img"></li>
								</ul>
								<div class="product-view-thumb-wrapper">
									<ul id="bx-pager" class="product-view-thumb">
										<li>
											<a class="thumb-item-link" data-slide-index="0" href="">
												<img src="{{ resize(config('larapen.core.picture.default'), 'small') }}" alt="img">
											</a>
										</li>
									</ul>
								</div>
							@endif
						</div>
						<!--ads-image-->
						
						
						@if (config('plugins.reviews.installed'))
							@if (view()->exists('reviews::ratings-single'))
								@include('reviews::ratings-single')
							@endif
						@endif
						

						<div class="ads-details">
							<ul class="nav nav-tabs">
								<li class="active">
									<a href="#tab-details" data-toggle="tab"><h4>{{ t('Ad Details') }}</h4></a>
								</li>
								@if (config('plugins.reviews.installed'))
									<li>
										<a href="#tab-{{ config('plugins.reviews.name') }}" data-toggle="tab">
											<h4>
												{{ trans('reviews::messages.Reviews') }}
												@if (isset($rvPost) and !empty($rvPost))
												({{ $rvPost->rating_count }})
												@endif
											</h4>
										</a>
									</li>
								@endif
							</ul>
							
							<!-- Tab panes -->
							<div class="tab-content">
								<div class="tab-pane active" id="tab-details">
									<div class="row" style="padding: 10px;">
										<div class="ads-details-info col-md-12 col-sm-12 col-xs-12 enable-long-words from-wysiwyg">
											
											<!-- Location -->
											<div class="detail-line-lite col-md-6 col-sm-6 col-xs-6">
												<div>
													<span><i class="fa fa-map-marker"></i> {{ t('Location') }}: </span>
													<span>
														<?php $attr = ['countryCode' => config('country.icode'), 'city' => slugify($post->city->name), 'id' => $post->city->id]; ?>
														<a href="{!! lurl(trans('routes.v-search-city', $attr), $attr) !!}">
															{{ $post->city->name }}
														</a>
													</span>
												</div>
											</div>
											@if (!in_array($post->category->type, ['non-salable']))
												<!-- Price / Salary -->
												<div class="detail-line-lite col-md-6 col-sm-6 col-xs-6">
													<div>
														<span>
															{{ (!in_array($post->category->type, ['job-offer', 'job-search'])) ? t('Price') : t('Salary') }}:
														</span>
														<span>
															@if ($post->price > 0)
																{!! \App\Helpers\Number::money($post->price) !!}
															@else
																{!! \App\Helpers\Number::money(' --') !!}
															@endif
															@if ($post->negotiable == 1)
																<small class="label label-success"> {{ t('Negotiable') }}</small>
															@endif
														</span>
													</div>
												</div>
											@endif
											<div style="clear: both;"></div>
											<hr>
											
											<!-- Description -->
											<div class="detail-line-content">
												{!! transformDescription($post->description) !!}
											</div>
											
											<!-- Custom Fields -->
											@include('post.inc.fields-values')
										
											<!-- Tags -->
											@if (!empty($post->tags))
												<?php $tags = explode(',', $post->tags); ?>
												@if (!empty($tags))
												<div style="clear: both;"></div>
												<div class="tags">
													<h4><i class="icon-tag"></i> {{ t('Tags') }}:</h4>
													@foreach($tags as $iTag)
														<?php $attr = ['countryCode' => config('country.icode'), 'tag' => $iTag]; ?>
														<a href="{{ lurl(trans('routes.v-search-tag', $attr), $attr) }}">
															{{ $iTag }}
														</a>
													@endforeach
												</div>
												@endif
											@endif
											
											<!-- Actions -->
											<div class="detail-line-action col-md-12 col-sm-12 text-center">
												<div class="col-md-4 col-sm-4 col-xs-4">
												@if (auth()->check())
													@if (auth()->user()->id == $post->user_id)
														<a href="{{ lurl('posts/' . $post->id . '/edit') }}">
															<i class="fa fa-pencil-square-o tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Edit') }}"></i>
														</a>
													@else
														@if ($post->email != '')
															<a data-toggle="modal" href="{{ $contactSellerURL }}">
																<i class="icon-mail-2 tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Send a message') }}"></i>
															</a>
														@else
															<i class="icon-mail-2" style="color: #dadada"></i>
														@endif
													@endif
												@else
													@if ($post->email != '')
														<a data-toggle="modal" href="{{ $contactSellerURL }}">
															<i class="icon-mail-2 tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Send a message') }}"></i>
														</a>
													@else
														<i class="icon-mail-2" style="color: #dadada"></i>
													@endif
												@endif
												</div>
												<div class="col-md-4 col-sm-4 col-xs-4">
													<a class="make-favorite" id="{{ $post->id }}">
														@if (auth()->check())
															@if (\App\Models\SavedPost::where('user_id', auth()->user()->id)->where('post_id', $post->id)->count() > 0)
																<i class="fa fa-heart tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Remove favorite') }}"></i>
															@else
																<i class="fa fa-heart-o" class="tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Save ad') }}"></i>
															@endif
														@else
															<i class="fa fa-heart-o" class="tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Save ad') }}"></i>
														@endif
													</a>
												</div>
												<div class="col-md-4 col-sm-4 col-xs-4">
													<a href="{{ lurl('posts/' . $post->id . '/report') }}">
														<i class="fa icon-info-circled-alt tooltipHere" data-toggle="tooltip" data-original-title="{{ t('Report abuse') }}"></i>
													</a>
												</div>
											</div>
										</div>
										
										<br>&nbsp;<br>
									</div>
								</div>
								
								@if (config('plugins.reviews.installed'))
									@if (view()->exists('reviews::comments'))
										@include('reviews::comments')
									@endif
								@endif
							</div>
							<!-- /.tab content -->
									
							<div class="content-footer text-left">
								@if (auth()->check())
									@if (auth()->user()->id == $post->user_id)
										<a class="btn btn-default" href="{{ lurl('posts/' . $post->id . '/edit') }}"><i class="fa fa-pencil-square-o"></i> {{ t('Edit') }}</a>
									@else
										@if ($post->email != '')
											<a class="btn btn-default" data-toggle="modal" href="{{ $contactSellerURL }}"><i class="icon-mail-2"></i> {{ t('Send a message') }} </a>
										@endif
									@endif
								@else
									@if ($post->email != '')
										<a class="btn btn-default" data-toggle="modal" href="{{ $contactSellerURL }}"><i class="icon-mail-2"></i> {{ t('Send a message') }} </a>
									@endif
								@endif
								@if ($post->phone_hidden != 1 and !empty($post->phone))
									<a href="{{ $phoneLink }}" {!! $phoneLinkAttr !!} class="btn btn-success showphone">
										<i class="icon-phone-1"></i>
										{!! $phone !!}{{-- t('View phone') --}}
									</a>
								@endif
							</div>
						</div>
					</div>
					<!--/.ads-details-wrapper-->
				</div>
				<!--/.page-content-->

				<div class="col-sm-3 page-sidebar-right">
					<aside>
						<div class="panel sidebar-panel panel-contact-seller">
							@if (auth()->check())
								@if (auth()->user()->getAuthIdentifier() == $post->user_id)
									<div class="panel-heading">{{ t('Manage Ad') }}</div>
								@else
									<div class="panel-heading">{{ t('Contact advertiser') }}</div>
								@endif
							@else
								<div class="panel-heading">{{ t('Contact advertiser') }}</div>
							@endif
							<div class="panel-content user-info">
								<div class="panel-body text-center">
									<div class="seller-info">
										@if (isset($post->contact_name) and $post->contact_name != '')
											@if (isset($post->user) and !empty($post->user))
												<h3 class="no-margin">
													<?php $attr = ['countryCode' => config('country.icode'), 'id' => $post->user->id]; ?>
													<a href="{{ lurl(trans('routes.v-search-user', $attr), $attr) }}">
														{{ $post->contact_name }}
													</a>
												</h3>
											@else
												<h3 class="no-margin">{{ $post->contact_name }}</h3>
											@endif
										@endif
										<p>
											{{ t('Location') }}:&nbsp;
											<strong>
												<?php $attr = ['countryCode' => config('country.icode'), 'city' => slugify($post->city->name), 'id' => $post->city->id]; ?>
												<a href="{!! lurl(trans('routes.v-search-city', $attr), $attr) !!}">
													{{ $post->city->name }}
												</a>
											</strong>
										</p>
										@if ($post->user and !is_null($post->user->created_at_ta))
											<p> {{ t('Joined') }}: <strong>{{ $post->user->created_at_ta }}</strong></p>
										@endif
									</div>
									<div class="user-ads-action">
										@if (auth()->check())
											@if (auth()->user()->id == $post->user_id)
												<a href="{{ lurl('posts/' . $post->id . '/edit') }}" data-toggle="modal" class="btn btn-default btn-block">
													<i class="fa fa-pencil-square-o"></i> {{ t('Update the Details') }}
												</a>
												<a href="{{ lurl('posts/' . $post->id . '/photos') }}" data-toggle="modal" class="btn btn-default btn-block">
													<i class="icon-camera-1"></i> {{ t('Update Photos') }}
												</a>
												@if (isset($countPackages) and isset($countPaymentMethods) and $countPackages > 0 and $countPaymentMethods > 0)
													<a href="{{ lurl('posts/' . $post->id . '/payment') }}" data-toggle="modal" class="btn btn-success btn-block">
														<i class="icon-ok-circled2"></i> {{ t('Make It Premium') }}
													</a>
												@endif
											@else
												@if ($post->email != '')
													<a href="{{ $contactSellerURL }}" data-toggle="modal" class="btn btn-default btn-block">
														<i class="icon-mail-2"></i> {{ t('Send a message') }}
													</a>
												@endif
												@if ($post->phone_hidden != 1 and !empty($post->phone))
													<a href="{{ $phoneLink }}" {!! $phoneLinkAttr !!} class="btn btn-success btn-block showphone">
														<i class="icon-phone-1"></i>
														{!! $phone !!}{{-- t('View phone') --}}
													</a>
												@endif
											@endif
										@else
											@if ($post->email != '')
												<a href="{{ $contactSellerURL }}" data-toggle="modal" class="btn btn-default btn-block">
													<i class="icon-mail-2"></i> {{ t('Send a message') }}
												</a>
											@endif
											@if ($post->phone_hidden != 1 and !empty($post->phone))
												<a href="{{ $phoneLink }}" {!! $phoneLinkAttr !!} class="btn btn-success btn-block showphone">
													<i class="icon-phone-1"></i>
													{!! $phone !!}{{-- t('View phone') --}}
												</a>
											@endif
										@endif
									</div>
								</div>
							</div>
						</div>
						
						@if (config('settings.single.show_post_on_googlemap'))
							<div class="panel sidebar-panel">
								<div class="panel-heading">{{ t('Location\'s Map') }}</div>
								<div class="panel-content">
									<div class="panel-body text-left" style="padding: 0;">
										<div class="ads-googlemaps">
											<iframe id="googleMaps" width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
										</div>
									</div>
								</div>
							</div>
						@endif
						
						@if (isVerifiedPost($post))
							@include('layouts.inc.social.horizontal')
						@endif
						
						<div class="panel sidebar-panel">
							<div class="panel-heading">{{ t('Safety Tips for Buyers') }}</div>
							<div class="panel-content">
								<div class="panel-body text-left">
									<ul class="list-check">
										<li> {{ t('Meet seller at a public place') }} </li>
										<li> {{ t('Check the item before you buy') }} </li>
										<li> {{ t('Pay only after collecting the item') }} </li>
									</ul>
                                    <?php $tipsLinkAttributes = getUrlPageByType('tips'); ?>
                                    @if (!str_contains($tipsLinkAttributes, 'href="#"') and !str_contains($tipsLinkAttributes, 'href=""'))
									<p>
										<a class="pull-right" {!! $tipsLinkAttributes !!}>
                                            {{ t('Know more') }}
                                            <i class="fa fa-angle-double-right"></i>
                                        </a>
                                    </p>
                                    @endif
								</div>
							</div>
						</div>
						<div class="panel sidebar-panel">
		
		
		                <?php
$advertising = \App\Models\Advertising::where('slug', 'top')->first();

?>
@if (!empty($advertising))
	
	<div class="">
		<div class="hidden-md hidden-sm hidden-xs ads-parent-responsive">
			<div class="text-center">
				{!! $advertising->tracking_code_large !!}
			</div>
		</div>
		<div class="hidden-lg hidden-xs ads-parent-responsive">
			<div class="text-center">
				{!! $advertising->tracking_code_medium !!}
			</div>
		</div>
		<div class="hidden-sm hidden-md hidden-lg ads-parent-responsive">
			<div class="text-center">
				{!! $advertising->tracking_code_small !!}
			</div>
		</div>
	</div>
@endif
		
		
		
		
		
		
		
						</div>
					</aside>
				</div>
			</div>

		</div>
		
		@include('home.inc.featured', ['firstSection' => false])
		@include('layouts.inc.advertising.bottom', ['firstSection' => false])
		@if (isVerifiedPost($post))
			@include('layouts.inc.tools.facebook-comments', ['firstSection' => false])
		@endif
		
	</div>
@endsection

@section('modal_message')
	@if (auth()->check() or config('settings.single.guests_can_contact_seller')=='1')
		@include('post.inc.compose-message')
	@endif
@endsection

@section('after_styles')
	<!-- bxSlider CSS file -->
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bxslider/jquery.bxslider.rtl.css') }}" rel="stylesheet"/>
	@else
		<link href="{{ url('assets/plugins/bxslider/jquery.bxslider.css') }}" rel="stylesheet"/>
	@endif
@endsection

@section('after_scripts')
    @if (config('services.googlemaps.key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}" type="text/javascript"></script>
    @endif

	<!-- bxSlider Javascript file -->
	<script src="{{ url('assets/plugins/bxslider/jquery.bxslider.min.js') }}"></script>
    
	<script>
		/* Favorites Translation */
        var lang = {
            labelSavePostSave: "{!! t('Save ad') !!}",
            labelSavePostRemove: "{!! t('Remove favorite') !!}",
            loginToSavePost: "{!! t('Please log in to save the Ads.') !!}",
            loginToSaveSearch: "{!! t('Please log in to save your search.') !!}",
            confirmationSavePost: "{!! t('Post saved in favorites successfully !') !!}",
            confirmationRemoveSavePost: "{!! t('Post deleted from favorites successfully !') !!}",
            confirmationSaveSearch: "{!! t('Search saved successfully !') !!}",
            confirmationRemoveSaveSearch: "{!! t('Search deleted successfully !') !!}"
        };
		
		$(document).ready(function () {
			/* Slider */
			var $mainImgSlider = $('.bxslider').bxSlider({
				speed: 1000,
				pagerCustom: '#bx-pager',
				adaptiveHeight: true
			});
			
			/* Initiates responsive slide */
			var settings = function () {
				var mobileSettings = {
					slideWidth: 80,
					minSlides: 2,
					maxSlides: 5,
					slideMargin: 5,
					adaptiveHeight: true,
					pager: false
				};
				var pcSettings = {
					slideWidth: 100,
					minSlides: 3,
					maxSlides: 6,
					pager: false,
					slideMargin: 10,
					adaptiveHeight: true
				};
				return ($(window).width() < 768) ? mobileSettings : pcSettings;
			};
			
			var thumbSlider;
			
			function tourLandingScript() {
				thumbSlider.reloadSlider(settings());
			}
			
			thumbSlider = $('.product-view-thumb').bxSlider(settings());
			$(window).resize(tourLandingScript);
			
			
			@if (config('settings.single.show_post_on_googlemap'))
				/* Google Maps */
				getGoogleMaps(
				'{{ config('services.googlemaps.key') }}',
				'{{ (isset($post->city) and !empty($post->city)) ? addslashes($post->city->name) . ',' . config('country.name') : config('country.name') }}',
				'{{ config('app.locale') }}'
				);
			@endif
            
			/* Keep the current tab active with Twitter Bootstrap after a page reload */
            /* For bootstrap 3 use 'shown.bs.tab', for bootstrap 2 use 'shown' in the next line */
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                /* save the latest tab; use cookies if you like 'em better: */
                localStorage.setItem('lastTab', $(this).attr('href'));
            });
            /* Go to the latest tab, if it exists: */
            var lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }
		})
	</script>
@endsection
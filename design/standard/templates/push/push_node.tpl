<h1 class="context-title">Netgen Push - {$node.name} - [{$node.class_name}]</h1>

{def
	$push_blocks = ezini('PushNodeSettings', 'Blocks', 'ngpush.ini')
	$active_blocks = ezini('PushNodeSettings', 'ActiveBlocks', 'ngpush.ini')
	$SiteURL = ezini('PushNodeSettings', 'SiteURL', 'ngpush.ini')
	$NodeURL = concat('http://', $SiteURL, '/', $node.url_alias)
	$bitly_login = ezini('bitly', 'login', 'ngpush.ini')
	$bitly_apikey = ezini('bitly', 'apikey', 'ngpush.ini')
	$facebook_appid = ezini('bitly', 'apikey', 'ngpush.ini')
	$dataypes = false()
	$attribute = false()
	$account = false()
	$attrId_title = false()
	$attrId_description = false()
	$attrId_image = false()}
{if $push_blocks}

{ezcss_require(array('ngpush.css'))}
{ezscript_require(array('json2.js','ngpush.js'))}

<script type="text/javascript">
var ngpush_node_id = {$node.node_id};
var ngpush_node_url_full = "{$NodeURL}";
var ngpush_node_url_short = "{$NodeURL}";
var ngpush_bitly_login = "{$bitly_login}";
var ngpush_bitly_apikey = "{$bitly_apikey}";
var ngpush_text_requesting = "{"Please wait, requesting..."|i18n("ngpush/status")}";
var ngpush_text_processed = "{"This entry has already been pushed."|i18n("ngpush/status")}";
var ngpush_text_maxlength_error = "{"Message is too long!"|i18n("ngpush/status")}";
</script>

<div id="ngpush-list">
{foreach $push_blocks as $index => $entry}
	{set $account = hash(
		'Type', ezini($entry, 'Type', 'ngpush.ini'),
		'Name', ezini($entry, 'Name', 'ngpush.ini')
	)}
	
	{switch match=$account.Type}
		{case match='twitter'}
			{set $account = hash(
				'Name',					ezini($entry, 'Name',								'ngpush.ini'),
				'Type',					ezini($entry, 'Type',								'ngpush.ini'),
				'tw_status',		cond(
													and(ezini_hasvariable($entry, 'attrId_status', 'ngpush.ini'), ezini($entry, 'attrId_status', 'ngpush.ini')[$node.class_identifier]),
													$node.data_map[ezini($entry, 'attrId_status', 'ngpush.ini')[$node.class_identifier]].content|wash)
			)}
			
			<table cellspacing="0" class="list ngpush-block type-{$account.Type}" id="ngpush-{$entry}">
				<thead>
					<tr>
						<th class="icon"><img title="Twitter status" alt="Twitter status" src={"ngpush-logo-twitter.png"|ezimage} /></th>
						<th class="name">{$account.Name}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="2">
							<div class="account-body float-break"{if $active_blocks|contains($entry)|not} style="display:none;"{/if}>
								<div class="status float-break">
									<div class="indicator"><div class="indicator-inactive"></div></div>
									<div class="message">{"Waiting..."|i18n("ngpush/status")}</div>
									<div class="control"></div>
								</div>
								
								<form action="" class="form">
									<p>
										<label class="maxlength">{"You have %number characters remaining."|i18n("ngpush/status", "", hash("%number", "<span>140</span>"))}</label>
										<textarea rows="1" cols="80" name="tw_status" spellcheck="false" class="maxlength maxlength-140">{$account.tw_status}</textarea>
									</p>
									<p>
										<input class="ngpush-account-id" type="hidden" value="{$entry}" />
										<input class="ngpush-account-type" type="hidden" value="{$account.Type}" />
										<input class="ngpush-status" type="hidden" value="" />
										<input type="button" value="{"Push"|i18n("ngpush/status")}" class="push defaultbutton" />
										<input class="button insert-link" name="full" type="button" value="{"Insert full link"|i18n("ngpush/ui")}" />
										<input class="button insert-link" name="short" type="button" value="{"Insert short link"|i18n("ngpush/ui")}" disabled="disabled" />
										<input class="button" type="reset" value="Reset" />
									</p>
								</form>
								
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		{/case}
		{case match='facebook_feed'}
			{set $account = hash(
				'Name',						ezini($entry, 'Name',								'ngpush.ini'),
				'Type',						ezini($entry, 'Type',								'ngpush.ini'),
				'AppId',					ezini($entry, 'AppId',							'ngpush.ini'),
				'EntityType',			ezini($entry, 'EntityType',					'ngpush.ini'),
				'Type',						ezini($entry, 'Type',								'ngpush.ini'),
				'fb_name',				cond(
														and(ezini_hasvariable($entry, 'attrId_name', 'ngpush.ini'), ezini($entry, 'attrId_name', 'ngpush.ini')[$node.class_identifier]),
														$node.data_map[ezini($entry, 'attrId_name', 'ngpush.ini')[$node.class_identifier]].content),
				'fb_description',	cond(
														and(ezini_hasvariable($entry, 'attrId_description', 'ngpush.ini'), ezini($entry, 'attrId_description', 'ngpush.ini')[$node.class_identifier]),
														$node.data_map[ezini($entry, 'attrId_description', 'ngpush.ini')[$node.class_identifier]].content.output.output_text|ngpush_xml_clean),
				'fb_message',			cond(
														and(ezini_hasvariable($entry, 'attrId_message', 'ngpush.ini'), ezini($entry, 'attrId_message', 'ngpush.ini')[$node.class_identifier]),
														$node.data_map[ezini($entry, 'attrId_message', 'ngpush.ini')[$node.class_identifier]].content.output.output_text|ngpush_xml_clean),
				'fb_picture',			cond(
														and(ezini_hasvariable($entry, 'attrId_picture', 'ngpush.ini'), ezini($entry, 'attrId_picture', 'ngpush.ini')[$node.class_identifier]),
														concat('http://',$SiteURL,'/content/download/',$node.data_map[ezini($entry, 'attrId_picture', 'ngpush.ini')[$node.class_identifier]].contentobject_id,'/',$node.data_map[ezini($entry, 'attrId_picture', 'ngpush.ini')[$node.class_identifier]].id,'/file/',$node.data_map[ezini($entry, 'attrId_picture', 'ngpush.ini')[$node.class_identifier]].content.original_filename)),
				'fb_link',				concat('http://', $SiteURL, '/', $node.url_alias)
			)}
			<table cellspacing="0" class="list ngpush-block type-{$account.Type}" id="ngpush-{$entry}">
				<thead>
					<tr>
						<th class="icon"><img title="Facebook feed" alt="Facebook feed" src={"ngpush-logo-facebook.png"|ezimage} /></th>
						<th class="name">{$account.Name}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="2">
							<div class="account-body float-break"{if $active_blocks|contains($entry)|not} style="display:none;"{/if}>
								<div class="status float-break">
									<div class="indicator"><div class="indicator-inactive"></div></div>
									<div class="message">{"Waiting..."|i18n("ngpush/status")}</div>
									<div class="control"></div>
								</div>
								
								<form action="">
									<p>
										<label><input name="fb_name" type="checkbox" checked="checked" /><strong>Name</strong></label>
										<input class="box" name="fb_name" type="text" value="{$account.fb_name}" size="80" /><br />
									</p>
									<p>
										<label><input name="fb_description" type="checkbox" checked="checked" /><strong>Description</strong></label>
										<textarea rows="5" cols="80" name="fb_description" spellcheck="false">{$account.fb_description}</textarea>
									</p>
									<p>
										<label><input name="fb_message" type="checkbox" checked="checked" /><strong>Message</strong></label>
										<textarea rows="5" cols="80" name="fb_message" spellcheck="false">{$account.fb_message}</textarea>
									</p>
									{if $account.fb_picture}
									<p>
										<label><input name="fb_picture" type="checkbox" checked="checked" /><strong>Picture</strong></label>
										<input name="fb_picture" type="hidden" value="{$account.fb_picture}" />
										<img src="{concat("/", $node.data_map[ezini($entry, 'attrId_picture', 'ngpush.ini')[$node.class_identifier]].content['ngpushthumb'].url)}" alt="" />
									</p>
									{/if}
									<p>
										<label><input name="fb_link" type="checkbox" checked="checked" /><strong>Link</strong></label>
										<input class="box" name="fb_link" type="text" value="{$account.fb_link}" size="80" />
									</p>
									<p>
										<input class="ngpush-account-id" type="hidden" value="{$entry}" />
										<input class="ngpush-account-type" type="hidden" value="{$account.Type}" />
										<input class="ngpush-facebook-appid" type="hidden" value="{$account.AppId}" />
										<input class="ngpush-facebook-entitytype" type="hidden" value="{$account.EntityType}" />
										<input class="ngpush-status" type="hidden" value="" />
										<input type="button" value="{"Push"|i18n("ngpush/status")}" class="push defaultbutton" />
										<input class="button" type="reset" value="Reset" />
									</p>
								</form>
								
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		{/case}
		
	{/switch}
{/foreach}

<div class="block">
	<input type="button" value="{"Push to all services"|i18n("ngpush/status")}" class="push-all defaultbutton" />
</div>

</div>

{else}

<p>{"No defined push services."|i18n("ngpush/status")}</p>

{/if}

{if !empty($img_background)}
  {* Sets The Profile Background *}
  <script>
    document.body.style.backgroundImage="url('{url_data_sys_mod}user/background/img/{username}/{img_background}')";
    document.body.style.backgroundRepeat='repeat';
    document.body.style.backgroundPosition='top center';
  </script>
{/if}

{if empty($error)}

  <ol id="toc">
    <li><a href="#general"><span>{lang 'Info'}</span></a></li>
    <li><a href="#map"><span>{lang 'Map'}</span></a></li>
    <li><a href="#friend"><span>{friend_link}</span></a></li>
    {if $is_logged && !$is_himself_profile}<li><a href="#mutual_friend"><span>{mutual_friend_link}</span></a></li>{/if}
    <li><a href="#picture"><span>{lang 'Photos'}</span></a></li>
    <li><a href="#video"><span>{lang 'Videos'}</span></a></li>
    <li><a href="#forum"><span>{lang 'Topics'}</span></a></li>
    <li><a href="#note"><span>{lang 'Notes'}</span></a></li>
    <li><a href="#visitor"><span>{lang 'Recently Viewed'}</span></a></li>
    {if $is_logged && !$is_himself_profile}<li><a rel="nofollow" href="{mail_link}"><span>{lang 'Send Message'}</span></a></li>{/if}
    {if $is_logged && !$is_himself_profile}<li><a rel="nofollow" href="{messenger_link}"><span>{lang 'Live Chat'}</span></a></li>{/if}
    {if $is_logged && !$is_himself_profile}<li><a ref="nofollow" href="{befriend_link}"><span>{lang 'Add Friend'}</span></a></li>{/if}
    {if $is_logged && !$is_himself_profile}<li><a href="{{ $design->url('love-calculator','main','index',$username) }}" title="{lang 'Love Calculator'}"><span>{lang 'Match'} <b class="pink2">&hearts;</b></span></a></li>{/if}
  </ol>

  <div class="content" id="general">
    {{ UserDesignCoreModel::userStatus($id) }}
    {{ $avatarDesign->lightBox($username, $first_name, $sex, 400) }}

    <p><span class="bold">{lang 'I am:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&match_sex='.$sex) }}">{lang $sex}</a></span></p>
    <div class="break"></div>

    {if !empty($match_sex)}
      <p><span class="bold">{lang 'Seeking a:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code) }}{match_sex_search}">{lang $match_sex}</a></span></p>
      <div class="break"></div>
    {/if}

    <p><span class="bold">{lang 'First name:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&first_name='.$first_name) }}">{first_name}</a></span></p>
    <div class="break"></div>

    {if !empty($middle_name)}
      <p><span class="bold">{lang 'Middle name:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&middle_name='.$middle_name) }}">{middle_name}</a></span></p>
      <div class="break"></div>
    {/if}

    {if !empty($last_name)}
      <p><span class="bold">{lang 'Last name:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&last_name='.$last_name) }}">{last_name}</a></span></p>
      <div class="break"></div>
    {/if}

    {if !empty($age)}
      <p><span class="bold">{lang 'Age:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&age='.$age) }}">{age}</a> <span class="gray">({birth_date})</span></span></p>
      <div class="break"></div>
    {/if}

    {if !empty($description)}
      <div class="profile_desc"><p class="bold">{lang 'Description:'}</p> <div class="quote italic">{description}</div></div>
    {/if}

    {* Profile's Fields *}
    {each $key => $val in $fields}

      {if $key != 'description' && $key != 'middleName' && !empty($val)}
        {{ $val = escape($val, true) }}

        {if $key == 'height'}
          <p><span class="bold">{lang 'Height:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&height='.$val) }}">{{ (new Framework\Math\Measure\Height($val))->display(true) }}</a></span></p>

        {elseif $key == 'weight'}
          <p><span class="bold">{lang 'Weight:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&weight='.$val) }}">{{ (new Framework\Math\Measure\Height($val))->display(true) }}</a></span></p>

        {elseif $key == 'country'}
          <p><span class="bold">{lang 'Country:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code) }}">{country}</a></span>&nbsp;&nbsp;<img src="{{ $design->getSmallFlagIcon($country_code) }}" title="{country}" alt="{country}" /></p>

        {elseif $key == 'city'}
          <p><span class="bold">{lang 'City / Town:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&city='.$city) }}">{city}</a></span></p>

        {elseif $key == 'state'}
          <p><span class="bold">{lang 'State:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&state='.$state) }}">{state}</a></span></p>

        {elseif $key == 'zipCode'}
          <p><span class="bold">{lang 'ZIP/Postal Code:'}</span> <span class="italic"><a href="{{ $design->url('user','browse','index', '?country='.$country_code.'&zip_code='.$val) }}">{val}</a></span></p>

        {elseif $key == 'website'}
          <p>{{ $design->favicon($val) }}&nbsp;&nbsp;<span class="bold">{lang 'Site / Blog:'}</span> <span class="italic">{{ $design->urlTag($val) }}</span></p>

        {elseif $key == 'socialNetworkSite'}
          <p>{{ $design->favicon($val) }}&nbsp;&nbsp;<span class="bold">{lang 'Social Network Profile:'}</span> <span class="italic">{{ $design->urlTag($val) }}</span></p>

        {else}
          {{ $lang_key = strtolower($key) }}

          {if strstr($key, 'url')}
            <p>{{ $design->favicon($val) }}&nbsp;&nbsp;<span class="bold">{lang $lang_key}</span> <span class="italic">{{ $design->urlTag($val) }}</span></p>
          {else}
            <p><span class="bold">{lang $lang_key}</span> <span class="italic">{val}</span></p>
          {/if}
        {/if}

        <div class="break"></div>
      {/if}

    {/each}

    {if !empty($join_date)}
      <p><span class="bold">{lang 'Join Date:'}</span> <span class="italic">{join_date}</span></p>
      <div class="break"></div>
    {/if}

    {if !empty($last_activity)}
      <p><span class="bold">{lang 'Last Activity:'}</span> <span class="italic">{last_activity}</span></p>
      <div class="break"></div>
    {/if}

    <p><span class="bold">{lang 'Views:'}</span> <span class="italic">{% Framework\Mvc\Model\Statistic::getView($id,'Members') %}</span></p>
    <div class="break"></div>

    {{ RatingDesignCore::voting($id,'Members') }}

  </div>

  <div class="content" id="map">
    <span class="bold">{lang 'Profile Map:'}</span>{map}
  </div>

  <div class="content" id="friend">
    <script>
      var url_friend_block = '{{ $design->url('user','friend','index',$username) }}';
      $('#friend').load(url_friend_block + ' #friend_block');
    </script>
  </div>

  {if $is_logged && !$is_himself_profile}
    <div class="content" id="mutual_friend">
      <script>
        var url_mutual_friend_block = '{{ $design->url('user','friend','mutual',$username) }}';
        $('#mutual_friend').load(url_mutual_friend_block + ' #friend_block');
      </script>
    </div>
  {/if}

  <div class="content" id="picture">
    <script>
      var url_picture_block = '{{ $design->url('picture','main','albums',$username) }}';
      $('#picture').load(url_picture_block + ' #picture_block');
    </script>
  </div>

  <div class="content" id="video">
    <script>
      var url_video_block = '{{ $design->url('video','main','albums',$username) }}';
      $('#video').load(url_video_block + ' #video_block');
    </script>
  </div>

  <div class="content" id="forum">
    <script>
      var url_forum_block = '{{ $design->url('forum','forum','showpostbyprofile',$username) }}';
      $('#forum').load(url_forum_block + ' #forum_block');
    </script>
  </div>

  <div class="content" id="note">
    <script>
      var url_note_block = '{{ $design->url('note','main','author',$username) }}';
      $('#note').load(url_note_block + ' #note_block');
    </script>
  </div>


  <div class="content" id="visitor">
    <script>
      var url_visitor_block = '{{ $design->url('user','visitor','index',$username) }}';
      $('#visitor').load(url_visitor_block + ' #visitor_block');
    </script>
  </div>

  <p class="center">{{ $design->like($username, $first_name, $sex) }} | {{ $design->report($id, $username, $first_name, $sex) }}</p>
  {{ $design->likeApi() }}

  <p>----------------------------------------</p>
  {{ CommentDesignCore::link($id, 'Profile') }}

  <script src="{url_static_js}tabs.js"></script>
  <script>tabs('p', ['general','map','friend',{if $is_logged && !$is_himself_profile}'mutual_friend',{/if}'picture','video','forum','note','visitor']);</script>

  {* Signup Popup *}
  {if !$is_logged && !AdminCore::auth()}
    {{ $design->staticFiles('js', PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_JS, 'signup_popup.js') }}
  {/if}

{else}

  <p class="center">{error}</p>

{/if}

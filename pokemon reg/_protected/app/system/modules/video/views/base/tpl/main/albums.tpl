<div class="center" id="video_block">

  {if empty($error)}

    {each $album in $albums}

      {{ $absolute_url = Framework\Mvc\Router\Uri::get('video','main','album',"$album->username,$album->name,$album->albumId") }}

      <div class="thumb_photo">

        <h4>{% Framework\Security\Ban\Ban::filterWord($album->name) %}</h4>
        <a href="{absolute_url}"><img src="{url_data_sys_mod}video/file/{% $album->username %}/{% $album->albumId %}/{% $album->thumb %}" alt="{% $album->name %}" title="{% $album->name %}" /></a>
        <p>{% nl2br(Framework\Security\Ban\Ban::filterWord($album->description)) %}</p>
        <p class="italic">{lang 'Views:'} {% Framework\Mvc\Model\Statistic::getView($album->albumId,'AlbumsVideos') %}</p>

        {if UserCore::auth() && $member_id == $album->profileId}
          <div class="small">
            <a href="{{ $design->url('video', 'main', 'editalbum', $album->albumId) }}">{lang 'Edit'}</a> |
            {{ LinkCoreForm::display(t('Delete'), 'video', 'main', 'deletealbum', array('album_id'=>$album->albumId)) }}
          </div>
        {/if}

        <p>
        {{ RatingDesignCore::voting($album->albumId,'AlbumsVideos') }}
        {{ $design->like($album->username,$album->firstName,$album->sex,$absolute_url) }} | {{ $design->report($album->profileId, $album->username, $album->firstName, $album->sex) }}
        </p>

      </div>

    {/each}

    {main_include 'page_nav.inc.tpl'}

  {else}

    <p>{error}</p>

  {/if}

  <p class="bottom"><a class="m_button" href="{{ $design->url('video', 'main', 'addalbum') }}">{lang 'Add a new album'}</a></p>

</div>

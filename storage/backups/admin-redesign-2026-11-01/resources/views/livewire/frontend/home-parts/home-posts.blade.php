</div>
<div class="home-posts-wrapper">
    @include('livewire.frontend.home-parts.slider',['pageabout'=> $pageabout])

    <div class="uk-container">
        <div class="uk-margin-medium-top">
             @include('livewire.frontend.home-parts.genealogy',['newsGenealogy'=> $newsGenealogy])
        </div>
    </div>
    <div class="investigation uk-margin-large-top">
        <div class="uk-container">
            <div class="uk-margin-medium-top">
                @include('livewire.frontend.home-parts.inventions',['eventPosts'=> $eventPosts])
            </div>
        </div>
    </div>
    <div class="news-section">
         <div class="uk-container">
            <div class="uk-margin-medium-top ">
             @include('livewire.frontend.home-parts.mainnews',['newsPosts'=> $newsPosts])
            </div>
        </div>
    </div>
    @if($pagemessage)
    <div class="message-section uk-margin-large-top">
         <div class="uk-container">
            <div class="message-item">
             @include('livewire.frontend.home-parts.message',['pagemessage'=> $pagemessage])
            </div>
        </div>
    </div>
    @endif
</div>

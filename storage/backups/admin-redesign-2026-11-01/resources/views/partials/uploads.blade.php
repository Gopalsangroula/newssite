<div id="imagePickerModal" class="uk-modal uk-modal-full" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <div class="uk-grid uk-grid-medium" uk-grid>
            <div class="uk-width-1-1@m">
                <div class="uk-flex uk-flex-between uk-flex-middle">
                    <h1 class="uk-margin-remove">Media Library</h1>
                    <button class="uk-button uk-button-default uk-modal-close" type="button">Close</button>
                </div>

                <!-- Upload Section -->
                <div class="uk-card uk-card-default uk-card-body uk-margin-top">
                    <h3>Upload New Image</h3>
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-expand">
                            <div uk-form-custom="target: true">
                                <input type="file" id="quickUploadInput" accept="image/jpeg,image/png,image/gif,image/webp">
                                <input class="uk-input" type="text" placeholder="Select an image" disabled>
                            </div>
                        </div>
                        <div class="uk-width-auto">
                            <button class="uk-button uk-button-primary" id="quickUploadBtn">Upload</button>
                        </div>
                    </div>
                    <div id="quickUploadProgress" class="uk-margin-top" style="display: none;">
                        <progress class="uk-progress" value="0" max="100"></progress>
                        <p class="uk-text-small uk-text-muted uk-margin-small-top">Uploading...</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="uk-margin-top">
                    <div class="uk-search uk-search-default uk-width-1-1">
                        <span uk-search-icon></span>
                        <input class="uk-search-input" id="mediaSearchInput" type="text" placeholder="Search images by name...">
                    </div>
                </div>

                <!-- Image Grid -->
                <div id="imageGrid" class="uk-grid-medium uk-child-width-1-5@m uk-child-width-1-2@s uk-margin-medium-top" uk-grid></div>

                <!-- Pagination -->
                <div class="uk-flex uk-flex-center uk-margin">
                    <ul class="uk-pagination" id="pagination"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #imageGrid .uk-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        overflow: hidden;
    }
    #imageGrid .uk-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    #imageGrid img {
        transition: transform 0.2s;
        height: 150px;
        object-fit: cover;
        width: 100%;
    }
    #imageGrid img:hover {
        transform: scale(1.05);
    }
</style>
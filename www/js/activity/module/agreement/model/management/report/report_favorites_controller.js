AgreementModelReportFavoritesManagementController = function (config) {
    // configurable {
    this.selector = '';
    this.add_to_favorites_url = '';
    this.remove_to_favorites_url = '';

    this.add_to_archive = '';
    this.delete_favorite_item = '';
    // }

    AgreementModelReportFavoritesManagementController.superclass.constructor.call(this, config);

    this.last_file_id = 0;
}

utils.extend(AgreementModelReportFavoritesManagementController, utils.Observable, {
    start: function () {
        this.initEvents();

        return this;
    },

    initEvents: function () {
        this.getValuesBlock().on('click', '.model-report-add-to-favorites', $.proxy(this.onAddToFavorites, this));
        this.getValuesBlock().on('click', '.model-report-remove-from-favorites', $.proxy(this.onRemoveFromFavorites, this));

        this.getFavoritesAddToAcrhive().click($.proxy(this.onFavoritesAddToArchive, this));
        this.getFavoritesDeleteItemLink().click($.proxy(this.onDeleteFavoritesItem, this));
    },

    onAddToFavorites: function (e) {
        e.preventDefault();
        $(e.target).closest('a.d-popup-uploaded-file').addClass('hvr-curl-top-right-no-hover');

        this.last_file_id = $(e.target).data('file-id');
        $.post(this.add_to_favorites_url,
            {
                reportId: $(e.target).data('report-id'),
                fileId: $(e.target).data('file-id'),
                modelTypeId: $(e.target).data('type-id')
            },
            $.proxy(this.onLoadAddToFavorites, this));
    },

    onLoadAddToFavorites: function (data) {
        this.getFavsContainer().html(data);
    },

    onRemoveFromFavorites: function (e) {
        e.preventDefault();
        $(e.target).closest('a.d-popup-uploaded-file').removeClass('hvr-curl-top-right-no-hover');

        this.last_file_id = $(e.target).data('file-id');
        $.post(this.remove_to_favorites_url,
            {
                reportId: $(e.target).data('report-id'),
                fileId: $(e.target).data('file-id'),
                modelTypeId: $(e.target).data('type-id')
            },
            $.proxy(this.onLoadRemoveFromFavorites, this));
    },

    onLoadRemoveFromFavorites: function (data) {
        this.getFavsContainer().html(data);
    },

    onFavoritesAddToArchive: function () {
        $.post(this.add_to_archive, {}, $.proxy(this.onLoadResultAddToArchive, this));
    },

    onLoadResultAddToArchive: function (data) {
        window.location.href = data.url;
    },

    onDeleteFavoritesItem: function (e) {
        if (confirm('Удалить ?')) {
            $.post(this.delete_favorite_item,
                {
                    id: $(e.target).data('id')
                },
                $.proxy(this.onDeleteItemResult, this));
        }
    },

    onDeleteItemResult: function (data) {
        if (data.success)
            $('.favorite-item-' + data.id).remove();
    },

    getContainer: function () {
        return $('#report-panel');
    },

    getFavsContainer: function() {
        return $('.favs-actions-container-' + this.last_file_id, this.getContainer());
    },

    getValuesBlock: function () {
        return $('.values', this.getContainer());
    },

    getFavoritesAddToAcrhive: function () {
        return $('.favorites-to-archive')
    },

    getFavoritesDeleteItemLink: function () {
        return $('.delete-favorite-item');
    }
});

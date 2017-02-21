/**
 * @category    MT
 * @package     MT_Widget
 * @copyright   Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */
;'use strict';

var FormVideoField = Class.create();
FormVideoField.prototype = {
    field: null,
    getBtn: null,
    indicator: null,
    initialize: function(fieldId, btnId, indicatorId){
        this.field = $(fieldId);
        this.getBtn = $(btnId);
        this.indicator = $(indicatorId);
    },
    getVideoId: function(url){
        var videoId, videoType;
        if (url.indexOf('youtube.com') > 0){
            videoType = 'youtube';
            videoId = url.split('v=')[1];
            videoId = videoId ? (videoId.indexOf('&') > 0 ? videoId.substring(0, videoId.indexOf('&')) : videoId) : videoId;
        }else if (url.indexOf('vimeo.com') > 0){
            videoType = 'vimeo';
            videoId = url.replace(/[^0-9]+/g, '');
        }
        return [videoType, videoId ? videoId.trim() : ''];
    },
    toggleBusy: function(flag){
        if (flag){
            disableElement(this.getBtn);
            this.indicator && this.indicator.show();
        }else{
            enableElement(this.getBtn);
            this.indicator && this.indicator.hide();
        }
    },
    onParallaxVideoYoutubeCallback: function(data){
        this.toggleBusy(false);
        var thumb = data['entry']['media$group']['media$thumbnail'][1]['url'];
        this.preview(thumb);
    },
    onParallaxVideoVimeoCallback: function(data){
        this.toggleBusy(false);
        var thumb = data[0]['thumbnail_medium'];
        this.preview(thumb);
    },
    preview: function(url){
        if (url){
            var imgElm = this.field.up().down('#videoThumbPreview');
            if (!imgElm){
                var img = new Element('img', {src: url, id: 'videoThumbPreview'});
                this.field.up().insert({bottom: img});
            }else imgElm.src = url;
        }
    },
    remove: function(){
        var imgElm = this.field.up().down('#videoThumbPreview');
        if (imgElm) imgElm.remove();
        this.field.value = '';
        this.field.focus();
    },
    search: function(){
        var videoData = this.getVideoId(this.field.value),
            urlAPI,
            head = $$('head')[0],
            script = new Element('script', {type:'text/javascript'});

        switch (videoData[0]){
            case 'youtube':
                urlAPI = 'https://gdata.youtube.com/feeds/api/videos/' +
                    videoData[1] + '?v=2&alt=json-in-script&callback=' + this.field.id + '.onParallaxVideoYoutubeCallback';
                break;
            case 'vimeo':
                urlAPI = 'http://vimeo.com/api/v2/video/' +
                    videoData[1] + '.json?callback=' + this.field.id + '.onParallaxVideoVimeoCallback';
                break;
        }
        if (urlAPI){
            script.src = urlAPI;
            head.appendChild(script);
            setTimeout(function(){
                this.toggleBusy(false);
            }.bind(this), 5000);
            this.toggleBusy(true);
        }
    }
};

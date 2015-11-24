$('.plupload').each(function () {
    var ele = this;
    var fsize = $(ele).data("fsize") ||"5mb";
    var fext = $(ele).data("ext") || "jpg,jpeg,gif,png";
    var ftitle = $(ele).data("title") || "Image files";
    var ccuploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: ele,
        url: global.sUploadUrl,
        flash_swf_url: global.static_url + '/plupload/Moxie.swf',
        silverlight_xap_url: global.static_url + '/plupload/Moxie.xap',
        filters: {
            max_file_size: fsize,
            mime_types: [
                {title: ftitle, extensions: fext}
            ],
            prevent_duplicates: true
        },
        init: {
            FilesAdded: function (up, files) {
                ccuploader.start();
            },
            FileUploaded: function (up, file, ret) {
                //console.dir(ret);
                eval('var tmp=' + ret.response);
                if (tmp.iError != 0) {
                    alert(tmp.msg);
                    return true;
                };
                var sCheck=$(ele).data("check");
                var iWidth=$(ele).data("twidth");
                var iHeight=$(ele).data("theight");
                var ret_iWidth=tmp.file.iWidth;
                var ret_iHeight=tmp.file.iHeight;
                var sBili=$(ele).data("bili");
                if(sCheck){
                   if(sCheck=="dayu") {
                       if(iWidth && iHeight ){
                       if(ret_iWidth<iWidth || ret_iHeight<iHeight){
                           alert("您上传的图片尺寸小于规定尺寸，请上传宽高大于"+iWidth+"*"+iHeight+"的图片");
                           return false;
                       }
                       if(sBili){
                           var afm=(parseFloat(ret_iWidth)/parseFloat(ret_iHeight)).toFixed(2);
                           var bfz=(parseFloat(iWidth)/parseFloat(iHeight)).toFixed(2);
                           if(afm!=bfz){
                               alert("您上传的图片尺寸比例不对");
                             return false;
                           }
                           
                       }
                     }
                   }
                   if(sCheck=="dengyu") {
                       if(iWidth && iHeight ){
                            if(ret_iWidth!=iWidth || ret_iHeight!=iHeight){
                                alert("您上传的图片尺寸不是规定尺寸，请上传宽高等于"+iWidth+"*"+iHeight+"的图片");
                                return false;
                            }
                       }
                       else{
                        if(ret_iWidth!=iWidth){
                            alert("您上传的图片尺寸不是规定尺寸，请上传宽等于"+iWidth+"的图片");
                            return false;
                        }
                     }
                   }  
                };
                var file = tmp.file.sKey + '.' + tmp.file.sExt;
                if ($(ele).data('target')) {
                    $($(ele).data('target')).val(file);
                }
                if ($(ele).data('img')) {
                    var height = $(ele).data('height') || 0;
                    var width = $(ele).data('width') || 0;
                    $($(ele).data('img')).attr('src', getDFSViewURL(file, width, height));
                }
                if ($(ele).data('callback')) {
                    eval($(ele).data('callback') + '(\'' + file + '\',' + tmp.file.iWidth + ', ' + tmp.file.iHeight + ')');
                }
            },
            Error: function (up, err) {
                alert(err.message);
            }
        }
    });
    ccuploader.init();
});

function getDFSViewURL(p_sFileKey, p_iWidth, p_iHeight, p_sOption, p_biz) {
    if (!p_sFileKey) {
        return '';
    }
    p_iWidth = p_iWidth || 0;
    p_iHeight = p_iHeight || 0;
    p_sOption = p_sOption || '';
    p_biz = p_biz || '';

    var sDfsViewUrl = global.sDfsViewUrl;
    if (p_biz == 'banner') {
        sDfsViewUrl += '/fjbanner';
    }
    var tmp = p_sFileKey.split('.');
    var p_sKey = tmp[0];
    var p_sExt = tmp[1];
    if (0 == p_iWidth && 0 == p_iHeight) {
        return sDfsViewUrl + '/' + p_sKey + '.' + p_sExt;
    } else {
        if ('' == p_sOption) {
            return sDfsViewUrl + '/' + p_sKey + '/' + p_iWidth + 'x' + p_iHeight + '.' + p_sExt;
        } else {
            return sDfsViewUrl + '/' + p_sKey + '/' + p_iWidth + 'x' + p_iHeight + '_' + p_sOption + '.' + p_sExt;
        }
    }
}
jQuery(document).ready(function(t) {
    t("input[type=submit]").submit(function() {
        t(this).attr("disabled", "disabled")
    }), jQuery("form").submit(function() {
        var e = t("input[name*='url']").val();
        "" !== e && (t(".jmj_seo_loader_container").show(250), t(".jmj_seo_test").hide(250), t.ajax({
            url: jmj_seo.ajax_url,
            type: "get",
            contentType: "application/json; charset=utf-8",
            data: {
                action: "jmj_seo_ajax_response",
                post_id: jmj_seo.postid,
                data_url: e
            },
            success: function(e) {
                t(".jmj_seo_loader_container").hide(250), t(".jmj_seo_test").replaceWith(e), t(".jmj_seo_test").show(250)
            },
            error: function(e) {
            	console.log('error: ' + e);
            }
        }))
    })
});
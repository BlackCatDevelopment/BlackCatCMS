window.BC_THEME_DEBUG = true;

document.addEventListener('DOMContentLoaded', function() {
    function bclog(msg, other) {
        BC_THEME_DEBUG && console.log("[BC_DEBUG] "+msg, other);
    }
    function groupnotecheck() {
        // at least one group selected?
        let len = document.querySelectorAll('input[name^=groups]:checked').length;
        if(len>=1) {
            groupnote.classList.add("hidden");
        } else {
            groupnote.classList.remove("hidden");
        }
    }

    //bclog("DOM loaded");

    // header dropdown
    [].forEach.call(document.querySelectorAll('.header__avatar'), function(el) {
        el.addEventListener('click', function() {
            var dropdown = this.querySelector(".dropdown");
            dropdown.classList.toggle("dropdown--active");
        });
    });
    // page tree
    [].forEach.call(document.querySelectorAll('.page_tree_item'), function(el) {
        el.addEventListener('click', function(e) {
            if(e.target.nodeName == 'LI') {
                this.classList.toggle("expanded");
                this.classList.toggle("collapsed");
                e.stopPropagation();
            }
        });
    });
    // ----- add / modify user -------------------------------------------------
    // image preview for avatar
    const fileInput = document.getElementById("avatar");
    if(fileInput) {
        const img = document.getElementById("avatarpreview");
        const imgSelect = document.getElementById("avatarselector");
        const avatardata = document.getElementById("avatardata");
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            avatardata.value = e.target.result;
        }
        fileInput.addEventListener('change', e => {
            const f = e.target.files[0];
            reader.readAsDataURL(f);
        });
        imgSelect.addEventListener('change', e => {
            if(imgSelect.value.length) {
                img.src = CAT_URL+'/media/.avatars/'+imgSelect.value;
            } else {
                img.src = "http://via.placeholder.com/75";
            }
        });
    }
    // toggle group hint
    const groupnote = document.querySelector(".group-notification");
    if(groupnote) {
        let grouplist = document.querySelectorAll("input[name^=groups]");
        [].forEach.call(grouplist, function(el) {
            el.addEventListener("change", function(e) {
                groupnotecheck();
            });
        });
        groupnotecheck();
    }

});
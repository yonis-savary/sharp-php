let openedNavbar = null;

document.addEventListener("DOMContentLoaded", async ()=>{
    let lang = SharpAssetsKit.lang;

    if (!lang.isMobile())
        return;

    let lastButtonBottom = 0;
    document.querySelectorAll(".navbar").forEach(nav => {

        let button = document.createElement("div");
        button.classList = "navbar-button";
        button.innerHTML = svg('list', 32)

        button.style.top = lastButtonBottom + "px";
        nav.appendChild(button);

        let box = button.getBoundingClientRect();
        lastButtonBottom = (box.top + box.height*1.1);

        console.log(button, nav);

        button.addEventListener("click", function(event){

            if (openedNavbar)
            {
                openedNavbar.classList.remove("active");
                if (nav !== openedNavbar)
                    nav.classList.add("active");
                openedNavbar = null;
            }
            else
            {
                nav.classList.add("active");
                openedNavbar = nav;
            }
        })
    });
});
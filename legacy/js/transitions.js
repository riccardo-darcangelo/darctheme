/* window.onload = () => {
    const transition_el = document.querySelector('.transition')
    const anchors = document.querySelectorAll('a')

    setTimeout(() => {
        transition_el.classList.remove('active-trans')
    }, 500)

    for (let i = 0; i < anchors.length; i++) {
        const anchor = anchors[i]

        anchor.addEventListener('click', e => {
            e.preventDefault();
            let target = e.target.href;

            transition_el.classList.add('active-trans')
            setTimeout(() => {
                window.location.href = target;
            }, 500)
        })
    }
}

window.onload = () => {
    const transition_el = document.querySelector('.transition')
    const anchors = document.querySelectorAll('a')

    setTimeout(() => {
        transition_el.classList.remove('active-trans')
    }, 500)

    for (let i = 0; i < anchors.length; i++) {
        const anchor = anchors[i]

        anchor.addEventListener('click', e => {
            e.preventDefault();
            let target = e.target.href;

            transition_el.classList.add('active-trans')
            setTimeout(() => {
                window.location.href = target;
            }, 500)
        })
    }
} */
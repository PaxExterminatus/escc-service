<template>
    <!-- Сама кнопка-переключатель живёт внутри липкого app-header (см. App.vue), рядом с
    бургером основного меню — не отдельной плавающей карточкой над страницей. -->
    <Teleport to="#app-header-end">
        <button class="anchor-menu-header" type="button" @click="open = !open">
            <span class="anchor-menu-icon"><span class="pi pi-compass"></span></span>
            <span class="anchor-menu-title">Навигация</span>
            <span class="anchor-menu-badge">{{ items.length + 1 }}</span>
            <span class="anchor-menu-chevron pi" :class="open ? 'pi-chevron-up' : 'pi-chevron-down'"></span>
        </button>
    </Teleport>

    <!-- Раскрывшийся список — уже не часть шапки (сломал бы её высоту/раскладку), поэтому
    оверлей, повешенный сразу под ней. -->
    <div v-if="open" class="anchor-menu-body" :style="{top: headerOffset}">
        <a class="anchor-menu-item" href="#" @click.prevent="scrollToTop">
            <span class="pi pi-arrow-up anchor-menu-item-icon"></span>
            <span>Наверх</span>
        </a>

        <a
            v-for="item in items"
            :key="item.id"
            class="anchor-menu-item"
            :class="{'anchor-menu-item-active': item.id === activeId}"
            :href="'#' + item.id"
            @click="onItemClick($event, item)"
        >
            <span :class="item.icon ?? 'pi pi-circle-fill'" class="anchor-menu-item-icon"></span>
            <span>{{ item.text }}</span>
        </a>
    </div>
</template>

<script setup>
/**
 * Липкое меню-якорь: само находит заголовки (h1) внутри переданного контейнера и строит по
 * ним список пунктов с плавной прокруткой, плюс фиксированный первый пункт "Наверх". Ничего
 * не хардкодит про конкретную страницу — появится новый заголовок в контейнере, появится и
 * пункт меню.
 *
 * Оформление — карточка-аккордеон: строка "Навигация [N] ⌄" сама по себе служит переключателем
 * (никакого отдельного бургера в шапке — это отдельная сущность от главного меню приложения,
 * см. App.vue), список раскрывается вниз под ней. Панель — оверлей (position: fixed) поверх
 * содержимого страницы, повешенный сразу под шапкой; на мобильном по умолчанию свёрнута, на
 * десктопе открыта.
 */
import {computed, defineProps, onBeforeUnmount, ref, watch} from 'vue'

const props = defineProps({
    // DOM-элемент (или CSS-селектор), внутри которого искать заголовки — обычно template ref
    // на контейнер с содержимым страницы. Без него — вся страница.
    container: {type: [Object, String], default: null},
    selector: {type: String, default: 'h1'},
});

// Насколько px от верха вьюпорта считается "текущим" заголовком — чуть больше высоты самого
// меню-якоря, чтобы активный пункт совпадал с тем, что реально видно под меню.
const ACTIVE_THRESHOLD = 96;

// PrimeFlex md-точка (см. resources/sass/app.sass) — тот же порог, по которому основное меню
// приложения решает, прятаться ли за бургер. На мобильном якорь-меню по умолчанию свёрнуто
// (место дороже), на десктопе — сразу открыто.
const open = ref(window.matchMedia('(min-width: 768px)').matches);

const items = ref([]);
const activeId = ref(null);

// Реальная высота .app-header, а не захардкоженное число: у неё только min-height (см.
// resources/sass/template/base.sass) — если содержимое шапки становится выше (перенос строки,
// другой набор кнопок на узком экране), фиксированный top оставлял бы верх панели под шапкой.
const headerOffset = ref('3.5rem');
let headerResizeObserver = null;

const updateHeaderOffset = () => {
    const header = document.querySelector('.app-header');
    if (!header) return;

    headerOffset.value = `${header.getBoundingClientRect().height}px`;

    // Тот же отступ — в scroll-padding-top документа, чтобы стандартный переход браузера по
    // #якорю (href, см. onItemClick/scrollToTop) сам подводил заголовок под шапку, а не под
    // неё. В resources/sass/template/base.sass значение захардкожено (3.5rem) на случай, если
    // этот компонент ещё не смонтирован — здесь оно только уточняется под реальную высоту.
    document.documentElement.style.scrollPaddingTop = headerOffset.value;
};

let mutationObserver = null;
let nextAnchorId = 0;

const resolveContainer = () => {
    if (!props.container) return document.body;

    return typeof props.container === 'string' ? document.querySelector(props.container) : props.container;
};

const scan = () => {
    const root = resolveContainer();

    if (!root) {
        items.value = [];
        return;
    }

    items.value = [...root.querySelectorAll(props.selector)]
        .filter((el) => el.textContent.trim())
        .map((el) => {
            if (!el.id) el.id = `page-anchor-${nextAnchorId++}`;

            // Иконка задаётся внутри заголовка отдельным <span class="pi ...">
            // (см. LazyPanelComponent) — тот же значок переносится в пункт меню.
            const icon = el.querySelector('[class*="pi-"]')?.className ?? null;

            return {id: el.id, text: el.textContent.trim(), icon, el};
        });

    updateActive();
};

/**
 * Активный пункт — последний заголовок, который уже проехал порог сверху (а не только
 * "видимый сейчас", как было бы через IntersectionObserver): иначе между двумя далёкими
 * друг от друга заголовками никакой пункт не подсвечивается, хотя мы явно внутри секции.
 *
 * Если докрутили до конца страницы — активен последний пункт всегда, даже если его заголовок
 * так и не доехал до порога (короткая последняя секция — физически прокрутить его выше порога
 * может быть просто нечем).
 */
const updateActive = () => {
    if (!items.value.length) {
        activeId.value = null;
        return;
    }

    const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;

    if (atBottom) {
        activeId.value = items.value[items.value.length - 1].id;
        return;
    }

    let current = items.value[0].id;

    for (const item of items.value) {
        if (item.el.getBoundingClientRect().top > ACTIVE_THRESHOLD) break;

        current = item.id;
    }

    activeId.value = current;
};

/**
 * Переход к якорю — обычная навигация браузера по #id (тот же самый механизм, что у любой
 * ссылки на якорь на странице): сам корректно упирается в конец документа без переполнения и
 * учитывает scroll-padding-top (см. updateHeaderOffset), поэтому никакой ручной анимации или
 * "запаса высоты" не требуется — раньше scrollIntoView({behavior:'smooth'}) с этим запасом
 * иногда перелистывал ниже, чем нужно (гонка между анимацией и таймером удаления запаса).
 *
 * Единственное, что здесь всё же нужно сделать самим: если заголовок принадлежит свёрнутой
 * вкладке (LazyPanelComponent), сначала раскрыть её (клик по заголовку) и подождать конца
 * transition — иначе браузер перейдёт по ссылке до того, как высота панели встанет на место, и
 * промахнётся. Если вкладка уже раскрыта — событию не мешаем, переход происходит нативно по
 * href, без preventDefault.
 */
const onItemClick = (event, item) => {
    const content = item.el.closest('.p-panel')?.querySelector('.p-toggleable-content');
    const wasCollapsed = content && content.offsetHeight === 0;

    if (!wasCollapsed) return;

    event.preventDefault();
    item.el.click();

    // Если хеш и так уже совпадает с целью — обычное присваивание hash не переходит повторно
    // (браузер не видит изменения), поэтому в этом случае просто scrollIntoView без анимации —
    // тот же "мгновенный" эффект, что и у обычного перехода по ссылке.
    const jump = () => {
        if (window.location.hash === `#${item.id}`) {
            item.el.scrollIntoView({block: 'start'});
        } else {
            window.location.hash = item.id;
        }
    };

    const onTransitionEnd = () => {
        content.removeEventListener('transitionend', onTransitionEnd);
        clearTimeout(fallback);
        jump();
    };

    // Фолбэк на случай, если transitionend не придёт (уменьшенная анимация, другой механизм
    // раскрытия) — тогда просто переходим по таймауту, не оставляя клик без реакции вовсе.
    const fallback = setTimeout(() => {
        content.removeEventListener('transitionend', onTransitionEnd);
        jump();
    }, 400);

    content.addEventListener('transitionend', onTransitionEnd);
};

const scrollToTop = () => window.scrollTo(0, 0);

const setupMutationObserver = () => {
    mutationObserver?.disconnect();

    const root = resolveContainer();
    if (!root) return;

    // Панели профиля монтируются/раскрываются по клиенту — пересобираем список заголовков
    // при любом изменении содержимого контейнера, а не только один раз при монтировании.
    mutationObserver = new MutationObserver(() => scan());
    mutationObserver.observe(root, {childList: true, subtree: true});
};

let scrollTicking = false;

const onScroll = () => {
    if (scrollTicking) return;

    scrollTicking = true;
    requestAnimationFrame(() => {
        updateActive();
        scrollTicking = false;
    });
};

watch(() => props.container, () => {
    scan();
    setupMutationObserver();
}, {immediate: true});

updateHeaderOffset();

const header = document.querySelector('.app-header');

if (header) {
    headerResizeObserver = new ResizeObserver(updateHeaderOffset);
    headerResizeObserver.observe(header);
}

window.addEventListener('scroll', onScroll, {passive: true});
window.addEventListener('resize', updateHeaderOffset);

onBeforeUnmount(() => {
    mutationObserver?.disconnect();
    headerResizeObserver?.disconnect();
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', updateHeaderOffset);
});
</script>

<style scoped>
.anchor-menu-header {
    --anchor-accent: #e0384f;
    --anchor-bg-header: #2c1520;
    --anchor-border: rgba(224, 56, 79, 0.45);

    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0.75rem;
    background: var(--anchor-bg-header);
    border: 1px solid var(--anchor-border);
    border-radius: 999px;
    cursor: pointer;
    color: #f2e8ea;
    font-family: inherit;
    font-size: 0.85rem;
}

.anchor-menu-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: var(--anchor-accent);
    color: #fff;
    flex-shrink: 0;
    font-size: 0.75rem;
}

.anchor-menu-title {
    font-weight: 700;
}

.anchor-menu-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.3rem;
    height: 1.3rem;
    padding: 0 0.35rem;
    border-radius: 999px;
    background: var(--anchor-accent);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
}

.anchor-menu-chevron {
    color: #cfa7ae;
}

/* Раскрывшийся список — уже не внутри шапки, оверлей поверх содержимого страницы, повешенный
   сразу под ней (см. headerOffset). */
.anchor-menu-body {
    --anchor-accent: #e0384f;
    --anchor-bg: #241019;
    --anchor-border: rgba(224, 56, 79, 0.45);

    position: fixed;
    right: 1rem;
    z-index: 150;
    width: 16rem;
    max-width: calc(100vw - 2rem);
    max-height: 60vh;
    overflow-y: auto;
    margin-top: 0.5rem;

    background: var(--anchor-bg);
    border: 1px solid var(--anchor-border);
    border-radius: 16px;
    padding: 0.4rem 0;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

.anchor-menu-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.9rem;
    color: #e7d9dd;
    text-decoration: none;
    border-left: 3px solid transparent;
    cursor: pointer;
    font-size: 0.9rem;
}

.anchor-menu-item:hover {
    background: rgba(255, 255, 255, 0.04);
}

.anchor-menu-item-icon {
    width: 1rem;
    text-align: center;
    color: var(--anchor-accent);
    flex-shrink: 0;
    font-size: 0.85rem;
}

.anchor-menu-item-active {
    border-left-color: var(--anchor-accent);
    background: rgba(224, 56, 79, 0.12);
    color: #fff;
    font-weight: 600;
}

.anchor-menu-item-active .anchor-menu-item-icon {
    color: #fff;
}
</style>

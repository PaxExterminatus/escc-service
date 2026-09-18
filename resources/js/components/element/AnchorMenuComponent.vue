<template>
    <!-- Тот же липкий app-header, где уже живёт бургер основного меню (см. App.vue) — оба
    бургера в одной строке: слева обычное меню, справа — оглавление текущей страницы. -->
    <Teleport to="#app-header-end">
        <Button
            icon="pi pi-bars"
            rounded
            severity="secondary"
            v-tooltip.left="'Оглавление'"
            @click="open = !open"
        />
    </Teleport>

    <div v-if="open" class="anchor-menu-panel">
        <Menu :model="menuModel"/>
    </div>
</template>

<script setup>
/**
 * Липкое меню-якорь: само находит заголовки (h1/h2) внутри переданного контейнера и строит
 * по ним список пунктов с плавной прокруткой. Ничего не хардкодит про конкретную страницу —
 * появится новый заголовок в контейнере, появится и пункт меню.
 *
 * Сама кнопка-бургер телепортируется в липкий app-header (см. App.vue) — там же, где бургер
 * основного меню, только справа. Панель списка — оверлей (position: fixed) поверх содержимого
 * страницы, повешенный сразу под шапкой; на мобильном по умолчанию свёрнута, на десктопе открыта.
 */
import {computed, defineProps, onBeforeUnmount, ref, watch} from 'vue'
import Menu from 'primevue/menu'
import Button from 'primevue/button'

const props = defineProps({
    // DOM-элемент (или CSS-селектор), внутри которого искать заголовки — обычно template ref
    // на контейнер с содержимым страницы. Без него — вся страница.
    container: {type: [Object, String], default: null},
    selector: {type: String, default: 'h1, h2'},
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

            // h2 (вложенные вкладки вроде "Доступные теги" внутри "Отправка сообщений") —
            // подпункт, отступается в меню от заголовков верхнего уровня (h1).
            const level = el.tagName === 'H2' ? 2 : 1;

            return {id: el.id, text: el.textContent.trim(), icon, el, level};
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
 * Прокрутка к заголовку — вынесена отдельно, чтобы звать её и сразу, и после анимации.
 *
 * Если заголовок ближе к концу страницы, чем высота вьюпорта, браузеру физически некуда
 * докрутить — контента ниже просто не хватает, чтобы поднять заголовок к самому верху
 * (упирается в максимум scrollHeight, заголовок застревает выше середины экрана). Временно
 * добавляем внизу страницы запас высоты в один экран — ровно на время прокрутки, — и убираем
 * его, когда анимация точно уже закончилась (обычная прокрутка успевшую позицию не потеряет:
 * заголовок никогда не на самом last экране, места и без запаса остаётся достаточно).
 */
const scrollToHeading = (item) => {
    const spacer = document.createElement('div');
    spacer.style.height = '100vh';
    spacer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(spacer);

    item.el.scrollIntoView({behavior: 'smooth', block: 'start'});

    setTimeout(() => spacer.remove(), 800);
};

/**
 * Если вкладка (LazyPanelComponent) свёрнута — заголовок кликом её раскрывает, ровно как
 * обычный клик пользователя по заголовку. Клик не шлётся, если уже раскрыта — иначе это был
 * бы переключатель "туда-обратно" и раскрытую вкладку он бы, наоборот, свернул.
 *
 * Раскрытие анимированно меняет высоту панели — если прокручивать сразу (или через один
 * кадр), целимся в позицию заголовка ДО анимации, и в процессе нижние заголовки "уезжают"
 * вниз, из-за чего страница выглядит так, будто сначала прыгнула не туда, а потом сама себя
 * поправила. Ждём конца transition у содержимого панели и только потом скроллим.
 */
const scrollTo = (item) => {
    const content = item.el.closest('.p-panel')?.querySelector('.p-toggleable-content');
    const wasCollapsed = content && content.offsetHeight === 0;

    if (!wasCollapsed) {
        scrollToHeading(item);
        return;
    }

    item.el.click();

    const onTransitionEnd = () => {
        content.removeEventListener('transitionend', onTransitionEnd);
        clearTimeout(fallback);
        scrollToHeading(item);
    };

    // Фолбэк на случай, если transitionend не придёт (уменьшенная анимация, другой механизм
    // раскрытия) — тогда просто скроллим по таймауту, не оставляя клик без реакции вовсе.
    const fallback = setTimeout(() => {
        content.removeEventListener('transitionend', onTransitionEnd);
        scrollToHeading(item);
    }, 400);

    content.addEventListener('transitionend', onTransitionEnd);
};

const menuModel = computed(() => items.value.map((item) => ({
    label: item.text,
    icon: item.icon,
    class: [
        item.id === activeId.value ? 'anchor-menu-item-active' : '',
        item.level === 2 ? 'anchor-menu-item-sub' : '',
    ].filter(Boolean).join(' '),
    command: () => scrollTo(item),
})));

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

window.addEventListener('scroll', onScroll, {passive: true});

onBeforeUnmount(() => {
    mutationObserver?.disconnect();
    window.removeEventListener('scroll', onScroll);
});
</script>

<style scoped>
/* Бургер живёт в липком app-header (см. App.vue) — сама панель всё равно оверлей поверх
   содержимого страницы, повешенный сразу под шапкой. */
.anchor-menu-panel {
    position: fixed;
    top: 3.5rem;
    right: 1rem;
    z-index: 150;
    width: 15rem;
    max-width: calc(100vw - 2rem);
}

.anchor-menu-panel :deep(.p-menu) {
    width: 100%;
}

.anchor-menu-panel :deep(.anchor-menu-item-active .p-menuitem-link) {
    color: var(--primary-color);
    font-weight: 600;
}

/* h2 (вложенные вкладки вроде "Доступные теги") — подпункт меню, с отступом от h1 */
.anchor-menu-panel :deep(.anchor-menu-item-sub .p-menuitem-link) {
    padding-left: 2rem;
    font-size: 0.85rem;
}
</style>

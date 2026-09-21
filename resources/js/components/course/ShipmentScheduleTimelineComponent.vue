<template>
    <div class="shipment-timeline" role="list" aria-label="График отправки">
        <div
            v-for="(item, index) in items"
            :key="index"
            class="shipment-node"
            :class="{'shipment-node-clickable': !item.virtual}"
            role="listitem"
            tabindex="0"
            @click="onSelect(item)"
            @keydown.enter="onSelect(item)"
        >
            <div class="shipment-marker" :class="markerClass(item)">
                <span :class="markerIcon(item)"></span>
            </div>

            <div class="shipment-body">
                <span class="shipment-date">{{ item.send_date }}</span>
                <span class="shipment-status" :class="{'shipment-status-planned': item.virtual}">{{ item.status }}</span>
                <span v-if="item.container_cost !== null" class="shipment-cost">
                    <span v-if="item.cost_estimated">≈</span>{{ formatCost(item.container_cost) }}
                </span>
            </div>
        </div>

        <div v-if="!items.length" class="text-color-secondary text-sm p-2">Нет данных о посылках.</div>
    </div>
</template>

<script setup>
/**
 * Горизонтальная лента отправок курса: реальные посылки (см. CourseController::containers())
 * слева, запланированные — справа, единой цепочкой слева-направо по датам. Стоимость плановых
 * строк — оценка (см. CourseScheduleCalculator, cost_estimated), помечена значком "≈", а не
 * показана как точная цифра.
 *
 * Специально без роутинга внутри — клик по реальной посылке просто эмитит 'select', а не сам
 * решает, куда переходить: этот компонент рассчитан на переиспользование и в кабинете (там
 * навигация к контейнеру устроена иначе, чем у оператора).
 *
 * Горизонтальный скролл — контейнеров может быть много (пройденный курс) и много плановых
 * (курс на годы вперёд), поэтому не перенос строк, а overflow-x с явными точками остановки
 * (scroll-snap) — так же устроены горизонтальные трекеры доставки (DHL/почта), где пользователь
 * прокручивает мимо уже прошедших шагов к текущему/будущим.
 */
import {defineEmits, defineProps} from 'vue'

const props = defineProps({
    /**
     * @type {{container_id: number|null, status: string, send_date: string, container_cost: number|null, cost_estimated: boolean, virtual: boolean}[]}
     */
    items: {type: Array, default: () => []},
});

const emit = defineEmits({
    select: null,
});

const onSelect = (item) => {
    if (item.virtual) return;

    emit('select', item);
};

const markerClass = (item) => {
    if (item.virtual) return 'shipment-marker-planned';

    return item.status === 'Sent' ? 'shipment-marker-sent' : 'shipment-marker-other';
};

const markerIcon = (item) => {
    if (item.virtual) return 'pi pi-clock';

    return item.status === 'Sent' ? 'pi pi-check' : 'pi pi-box';
};

const formatCost = (value) => Number(value).toFixed(2);
</script>

<style scoped>
.shipment-timeline {
    display: flex;
    align-items: flex-start;
    overflow-x: auto;
    scroll-snap-type: x proximity;
    padding: 0.5rem 0.25rem 1rem;
    gap: 0;
}

.shipment-node {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 8.5rem;
    scroll-snap-align: start;
    cursor: default;
    outline: none;
}

.shipment-node-clickable {
    cursor: pointer;
}

.shipment-node-clickable:hover .shipment-marker,
.shipment-node-clickable:focus-visible .shipment-marker {
    box-shadow: 0 0 0 3px var(--primary-color, #22d3ee);
}

/* Соединительная линия — не отдельный элемент, а псевдоэлементы самого узла: левая половина
   (::before) и правая половина (::after) каждого узла, обе на высоте центра маркера. У соседних
   узлов половины стыкуются ровно на границе между ними — надёжнее, чем тянуть один элемент
   через оба узла негативными отступами. */
.shipment-node::before,
.shipment-node::after {
    content: '';
    position: absolute;
    top: 1.125rem;
    height: 2px;
    width: 50%;
    background: var(--surface-border, rgba(255, 255, 255, 0.15));
}

.shipment-node::before {
    left: 0;
}

.shipment-node::after {
    right: 0;
}

.shipment-node:first-child::before,
.shipment-node:last-child::after {
    display: none;
}

.shipment-marker {
    position: relative;
    z-index: 1;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    background: var(--surface-card, #1e1e2f);
    border: 2px solid var(--surface-border, rgba(255, 255, 255, 0.2));
    color: var(--text-color-secondary, #aaa);
    flex-shrink: 0;
    transition: box-shadow 0.15s ease;
}

.shipment-marker-sent {
    background: rgba(74, 222, 128, 0.15);
    border-color: #4ade80;
    color: #4ade80;
}

.shipment-marker-other {
    background: rgba(96, 165, 250, 0.15);
    border-color: #60a5fa;
    color: #60a5fa;
}

.shipment-marker-planned {
    background: transparent;
    border-style: dashed;
    color: var(--text-color-secondary, #999);
}

.shipment-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    margin-top: 0.5rem;
    text-align: center;
}

.shipment-date {
    font-weight: 600;
    font-size: 0.85rem;
}

.shipment-status {
    font-size: 0.75rem;
    color: var(--text-color-secondary, #999);
}

.shipment-status-planned {
    font-style: italic;
}

.shipment-cost {
    font-size: 0.8rem;
}
</style>

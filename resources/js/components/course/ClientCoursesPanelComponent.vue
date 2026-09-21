<template>
    <LoadingBlock v-if="loading" small/>
    <div v-else-if="!nodes.length" class="text-color-secondary text-sm p-2">
        У клиента нет курсов.
    </div>
    <TreeTable
        v-else
        :value="nodes"
        dataKey="key"
        lazy
        :selectionMode="selectable ? 'single' : null"
        v-model:expandedKeys="expandedKeys"
        @node-expand="onExpand"
        @node-select="onNodeSelect"
    >
        <Column field="name" header="Курс / Посылка" expander>
            <template #body="{node}">
                <!-- Курс раскрывается кликом по всей строке, не только по значку-стрелке —
                     иначе легко не заметить, что строка вообще раскрывается. -->
                <span v-if="!node.data.containerId" class="cursor-pointer" @click="toggleExpand(node)">{{ node.data.name }}</span>
                <span v-else>{{ node.data.name }}</span>
            </template>
        </Column>
        <Column field="status" header="Статус"/>
        <Column field="send_date" header="Дата"/>
        <template v-if="showFinance">
            <Column header="Начислено">
                <template #body="{node}">{{ node.data.invoiced.toFixed(2) }}</template>
            </Column>
            <Column header="Оплачено">
                <template #body="{node}">{{ node.data.received.toFixed(2) }}</template>
            </Column>
            <Column header="Возврат / потери">
                <template #body="{node}">
                    <span v-tooltip.top="node.data.lost > 0 ? `Возврат: ${node.data.returned.toFixed(2)}, потери: ${node.data.lost.toFixed(2)}` : null">
                        {{ (node.data.returned + node.data.lost).toFixed(2) }}
                    </span>
                </template>
            </Column>
            <Column header="Баланс">
                <template #body="{node}">
                    <span :class="node.data.balance > 0 ? 'text-red-400' : 'text-green-400'">{{ node.data.balance.toFixed(2) }}</span>
                </template>
            </Column>
        </template>
        <Column header="">
            <template #body="{node}">
                <router-link
                    v-if="node.data.containerId"
                    :to="{name: 'containerShow', params: {id: node.data.containerId}}"
                    v-tooltip.left="'Открыть контейнер'"
                    @click.stop
                >
                    <span class="pi pi-external-link"></span>
                </router-link>
                <router-link
                    v-else
                    :to="{name: 'courseShow', params: {id: node.data.subId}}"
                    v-tooltip.left="'Открыть курс'"
                    @click.stop
                >
                    <span class="pi pi-external-link"></span>
                </router-link>
            </template>
        </Column>
    </TreeTable>
</template>

<script setup>
/**
 * Курсы клиента и их посылки — раньше два отдельных DataTable (список курсов сверху, посылки
 * выбранного курса под ним), теперь одно дерево: посылки — дочерние строки своего курса,
 * подгружаются лениво при раскрытии (см. onExpand). Курс сам по себе не выбираем
 * (TreeNode.selectable по умолчанию false) — выбор имеет смысл только для посылки.
 */
import {defineEmits, defineExpose, defineProps, onMounted, ref, watch} from 'vue'
import TreeTable from 'primevue/treetable'
import Column from 'primevue/column'
import {LoadingBlock} from 'element'
import {showError} from 'app/toast'
import {courseAPI} from './CourseAPI.js'

const props = defineProps({
    clientId: [String, Number],
    // В пикере тегов — выбор посылки кликом по строке закрывает диалог; в панели профиля выбор
    // не нужен, там переход к контейнеру идёт по иконке "Открыть".
    selectable: {type: Boolean, default: false},
    // Колонки начислено/оплачено/возврат-потери/баланс — нужны на панели профиля, но перегружают
    // компактный диалог-пикер посылки (там важен только выбор, не бухгалтерия).
    showFinance: {type: Boolean, default: true},
});

const emit = defineEmits({
    'select-container': null,
});

const nodes = ref([]);
const loading = ref(false);
const expandedKeys = ref({});

const courseNode = (course) => ({
    key: `course-${course.sub_id}`,
    leaf: false,
    data: {
        name: course.course_name,
        status: null,
        send_date: course.next_send_date,
        containerId: null,
        subId: course.sub_id,
        invoiced: course.invoiced,
        received: course.received,
        returned: course.returned,
        lost: course.lost,
        balance: course.balance,
    },
    children: [],
});

/** @param {string} courseName — тянется в узел посылки, чтобы показать "какой курс" при выборе */
const containerNode = (container, courseName) => ({
    key: `container-${container.container_id}`,
    selectable: props.selectable,
    data: {
        name: container.container_code,
        status: container.status,
        send_date: container.send_date,
        containerId: container.container_id,
        courseName,
        invoiced: container.invoiced,
        received: container.received,
        returned: container.returned,
        lost: container.lost,
        balance: container.balance,
    },
});

const load = () => {
    if (!props.clientId) return Promise.resolve();

    loading.value = true;

    return courseAPI.courses(props.clientId)
        .then((response) => {
            nodes.value = response.data.courses.map(courseNode);
        })
        .catch(() => showError('Не удалось загрузить курсы клиента.'))
        .finally(() => {
            loading.value = false;
        });
};

watch(() => props.clientId, load);
onMounted(load);

/** Посылки курса — подгружаются по месту, узел временно помечается своим loading */
const loadContainers = (node) => {
    node.loading = true;

    return courseAPI.containers(node.data.subId)
        .then((response) => {
            const containers = response.data.containers;
            node.children = containers.map((container) => containerNode(container, node.data.name));

            // Курс почти всегда даёт ровно одну посылку — тогда раскрытия курса уже достаточно
            // для вставки тега, не заставляя ещё раз выбирать из списка на одну строку.
            if (props.selectable && containers.length === 1) {
                emit('select-container', {...containers[0], course_name: node.data.name});
            }
        })
        .catch(() => showError('Не удалось загрузить посылки курса.'))
        .finally(() => {
            node.loading = false;
        });
};

const onExpand = (node) => {
    if (node.children.length) return;

    loadContainers(node);
};

/** Раскрытие курса кликом по строке — то же самое, что клик по значку-стрелке TreeTable */
const toggleExpand = (node) => {
    const wasExpanded = !!expandedKeys.value[node.key];

    expandedKeys.value = {...expandedKeys.value, [node.key]: !wasExpanded};

    if (!wasExpanded) onExpand(node);
};

const onNodeSelect = (node) => {
    if (!node.data.containerId) return;

    emit('select-container', {
        container_id: node.data.containerId,
        container_code: node.data.name,
        status: node.data.status,
        send_date: node.data.send_date,
        course_name: node.data.courseName,
    });
};

// Кнопка "Обновить принудительно" на панели профиля — обновляет список курсов и посылки уже
// раскрытых курсов (а не только сам список, иначе раскрытая посылка осталась бы устаревшей).
defineExpose({
    reload: () => load().then(() => Promise.all(
        nodes.value.filter((node) => expandedKeys.value[node.key]).map(loadContainers)
    )),
});
</script>

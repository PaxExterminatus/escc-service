const operatorMenu = [
    {
        label: 'Base',
        icon:'pi pi-fw pi-home',
        to: {name: 'base'},
    },
    {
        label: 'Сообщения',
        icon:'pi pi-fw pi-comment',
        to: null,
        items: [
            {
                label: 'SMS',
                icon:'pi pi-fw pi-comment',
                to: {name: 'messages-sending'},
            }
        ],
    }
]

export {
    operatorMenu,
}

# HSR-Project
HSR Project

Este documento descreve as funções de uma API (Programação de Aplicativos).
Interface).

Esta dividida em 2 grupos: Non-Agent API e Agent API

No Vicidial, há dois tipos de APIs: Non-Agent API e Agent API . Aqui está uma breve explicação de cada uma:
Non-Agent API : A Non-Agent API é usada para interações em nível de sistema, como gerenciamento de campanhas, listas e servidores. Essa API é normalmente usada por administradores de sistema ou desenvolvedores para automatizar tarefas, integrar com outros sistemas ou criar aplicativos personalizados.
A API não agente fornece acesso a funções como:
Gerenciamento de campanha (criar, atualizar, excluir)
Gerenciamento de listas (criar, atualizar, excluir)
Gerenciamento de servidor (adicionar, remover, atualizar)
Relatórios e análises
Configuração do sistema
API do agente : A API do agente é usada para interações em nível de agente, como gerenciar sessões de agente, fazer chamadas e atualizar o status do agente. Essa API é normalmente usada por agentes ou supervisores para interagir com o sistema Vicidial.
A API do agente fornece acesso a funções como:
Login e logout do agente
Gerenciamento de chamadas (fazer, atender, desligar, transferir)
Atualizações de status do agente (disponível, indisponível, de plantão)
Gravação e reprodução de chamadas
Monitoramento de chamadas em tempo real
A principal diferença entre as duas APIs é o nível de acesso e o tipo de interações que elas fornecem. A Non-Agent API é focada no gerenciamento de nível de sistema, enquanto a Agent API é focada em interações de nível de agente.
Ao escolher qual API usar, considere o seguinte:
Se você precisar automatizar tarefas no nível do sistema ou integrar com outros sistemas, use a API sem agente.
Se você precisar interagir com o sistema Vicidial como agente ou supervisor, use a API do agente.




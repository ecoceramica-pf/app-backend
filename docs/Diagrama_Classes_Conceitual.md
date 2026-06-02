# Diagrama de Classes – Modelo Conceitual

Este diagrama apresenta as classes de conceito e as principais funcionalidades do sistema **EcoCeramica**, baseadas nos requisitos funcionais documentados e nas APIs desenvolvidas no backend Laravel. O foco é na abstração do domínio e nas regras de negócio, sem detalhar classes de infraestrutura do framework.

```mermaid
classDiagram
    class Usuario {
        +String nome
        +String email
        +String documento (CPF/CNPJ)
        +String telefone
        +TipoPerfil tipo_perfil
        +cadastrar()
        +login()
        +logout()
        +consultarPerfil()
        +atualizarPerfil()
        +configurarDisponibilidade()
    }

    class TipoPerfil {
        <<enumeration>>
        fabrica
        coletor
        admin
    }

    class Endereco {
        +String logradouro
        +String numero
        +String bairro
        +String cidade
        +String uf
        +String cep
        +String complemento
        +Point localizacao
        +cadastrar()
        +listar()
        +atualizar()
        +excluir()
    }

    class Material {
        +String nome
        +String descricao
        +Boolean ativo
        +Boolean cortante
        +listar()
    }

    class OfertaResiduo {
        +UUID uuid
        +Decimal quantidade_kg
        +Integer quantidade_cacamba
        +String observacoes
        +DateTime data_publicacao
        +OfertaStatus status
        +cadastrarOferta()
        +listarMural()
        +filtrarPorMaterial()
        +listarMinhasOfertas()
        +atualizar()
        +excluir()
    }

    class OfertaStatus {
        <<enumeration>>
        disponivel
        em_processo
        concluido
        cancelado
    }

    class OfertaImagem {
        +String imagem
        +BigInteger tamanho_arquivo
        +anexarImagem()
    }

    class FabricaDisponibilidade {
        +JSON dias_semana
        +SmallInt duracao_coleta_min
        +SmallInt antecedencia_minima_dias
        +SmallInt max_coletas_dia
        +configurar()
        +atualizar()
    }

    class FabricaFaixaHorario {
        +Time hora_inicio
        +Time hora_fim
    }

    class FabricaBloqueio {
        +Date data_bloqueio
        +String motivo
    }

    class Coleta {
        +DateTime data_reserva
        +DateTime data_agendamento
        +DateTime data_conclusao
        +DateTime confirmacao_fabrica
        +DateTime confirmacao_coletor
        +String status (pendente, agendado, concluido, cancelado, recusado)
        +String observacoes
        +reservarResiduo()
        +agendarColeta()
        +confirmarRetiradaFabrica()
        +confirmarRetiradaColetor()
        +recusarColeta()
        +cancelarColeta()
        +listarMinhasColetas()
    }

    class Dashboard {
        +Decimal total_reaproveitado_kg
        +Integer total_reaproveitado_cacamba
        +calcularImpacto()
    }

    %% ─── Relacionamentos ───
    Usuario --> TipoPerfil : usa
    OfertaResiduo --> OfertaStatus : usa

    Usuario "1" -- "*" Endereco : possui
    Usuario "1" -- "*" OfertaResiduo : publica (como Fábrica)
    Usuario "1" -- "*" Coleta : realiza (como Coletor)
    Usuario "1" -- "0..1" FabricaDisponibilidade : configura (como Fábrica)

    OfertaResiduo "*" -- "1" Material : categorizada por
    OfertaResiduo "1" -- "1" Endereco : localizada em
    OfertaResiduo "1" -- "*" OfertaImagem : possui fotos
    OfertaResiduo "1" -- "0..1" Coleta : é alvo de

    FabricaDisponibilidade "1" -- "*" FabricaFaixaHorario : possui
    FabricaDisponibilidade "1" -- "*" FabricaBloqueio : possui

    Dashboard ..> OfertaResiduo : consulta totais
```

## Mapeamento de Requisitos Funcionais

| Requisito | Classe(s) Envolvida(s) | Observações |
|-----------|------------------------|-------------|
| **RF01** – Cadastro de Usuários e Perfis | `Usuario`, `TipoPerfil` | Cadastro com validação de CPF/CNPJ e perfil (Fábrica, Coletor ou Admin) |
| **RF02** – Login e Autenticação | `Usuario` | Autenticação via Sanctum (token); isolamento de dados por usuário |
| **RF03** – Cadastro de Oferta de Resíduo | `OfertaResiduo`, `Material` | Registro de material com quantidade em kg e/ou caçambas, com possibilidade de observações |
| **RF04** – Mural de Resíduos Disponíveis | `OfertaResiduo`, `Endereco` | Listagem pública filtrada por status "disponível" |
| **RF05** – Reserva/Agendamento de Coleta | `Coleta`, `OfertaResiduo`, `FabricaDisponibilidade` | Muda status de "disponível" para "em processo" ao reservar/agendar. Respeita disponibilidade configurada |
| **RF06** – Confirmação e Conclusão de Retirada | `Coleta` | Dupla confirmação (fábrica + coletor) via DateTime; conclui ao ter ambas. Fábrica pode recusar |
| **RF07** – Histórico de Descarte/Coleta | `Coleta`, `OfertaResiduo` | Endpoints "minhas-coletas" e "minhas-ofertas" |
| **RF08** – Gerenciamento de Endereços | `Endereco` | CRUD completo com campo de geolocalização (Point) e complemento |
| **RF09** – Filtro por Tipo de Material | `OfertaResiduo`, `Material` | Filtragem do mural por `material_id` |
| **RF10** – Dashboard de Impacto | `Dashboard` | Soma de `quantidade_kg` e `quantidade_cacamba` das ofertas concluídas |
| **RF11** – Configuração de Disponibilidade | `FabricaDisponibilidade`, `FabricaFaixaHorario`, `FabricaBloqueio` | Fábrica define dias da semana, faixas de horário e datas de bloqueio para permitir coletas |

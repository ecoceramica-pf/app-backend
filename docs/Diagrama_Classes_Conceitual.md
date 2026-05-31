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

    class Coleta {
        +DateTime data_reserva
        +DateTime data_conclusao
        +DateTime confirmacao_fabrica
        +DateTime confirmacao_coletor
        +String status (pendente, concluido, cancelado)
        +reservarResiduo()
        +confirmarRetiradaFabrica()
        +confirmarRetiradaColetor()
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

    OfertaResiduo "*" -- "1" Material : categorizada por
    OfertaResiduo "1" -- "1" Endereco : localizada em
    OfertaResiduo "1" -- "*" OfertaImagem : possui fotos
    OfertaResiduo "1" -- "0..1" Coleta : é alvo de

    Dashboard ..> OfertaResiduo : consulta totais
```

## Mapeamento de Requisitos Funcionais

| Requisito | Classe(s) Envolvida(s) | Observações |
|-----------|------------------------|-------------|
| **RF01** – Cadastro de Usuários e Perfis | `Usuario`, `TipoPerfil` | Cadastro com validação de CPF/CNPJ e perfil (Fábrica, Coletor ou Admin) |
| **RF02** – Login e Autenticação | `Usuario` | Autenticação via Sanctum (token); isolamento de dados por usuário |
| **RF03** – Cadastro de Oferta de Resíduo | `OfertaResiduo`, `Material` | Registro de material com quantidade em kg e/ou caçambas |
| **RF04** – Mural de Resíduos Disponíveis | `OfertaResiduo`, `Endereco` | Listagem pública filtrada por status "disponível" |
| **RF05** – Reserva/Agendamento de Coleta | `Coleta`, `OfertaResiduo` | Muda status de "disponível" para "em processo" ao reservar |
| **RF06** – Confirmação de Retirada | `Coleta` | Dupla confirmação (fábrica + coletor) via DateTime; conclui ao ter ambas |
| **RF07** – Histórico de Descarte/Coleta | `Coleta`, `OfertaResiduo` | Endpoints "minhas-coletas" e "minhas-ofertas" |
| **RF08** – Gerenciamento de Endereços | `Endereco` | CRUD completo com campo de geolocalização (Point) |
| **RF09** – Filtro por Tipo de Material | `OfertaResiduo`, `Material` | Filtragem do mural por `material_id` |
| **RF10** – Dashboard de Impacto | `Dashboard` | Soma de `quantidade_kg` e `quantidade_cacamba` das ofertas concluídas |

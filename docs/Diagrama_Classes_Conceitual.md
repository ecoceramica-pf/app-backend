# Diagrama de Classes - Modelo Conceitual

Este diagrama apresenta as classes de conceito e as principais funcionalidades do sistema, baseadas nos requisitos funcionais e nas rotas das APIs desenvolvidas. O objetivo é focar na abstração e nas regras de negócio, sem se aprofundar em detalhes de infraestrutura ou do framework.

```mermaid
classDiagram
    class Usuario {
        +String nome
        +String email
        +String documento (CPF/CNPJ)
        +String tipo_perfil (Fábrica / Coletor)
        +cadastrar()
        +login()
        +consultarPerfil()
    }

    class Endereco {
        +String logradouro
        +String numero
        +String bairro
        +String cidade
        +String uf
        +String cep
        +gerenciarEnderecos()
    }

    class Material {
        +String nome (Ex: Gesso, Cerâmica)
        +String descricao
        +listarMateriais()
    }

    class OfertaResiduo {
        +Double quantidade
        +String unidade_medida (kg, caçamba)
        +String status (Disponível, Em Processo, etc)
        +cadastrarOferta()
        +listarMuralDisponiveis()
        +filtrarPorMaterial()
    }

    class OfertaImagem {
        +String url_imagem
        +anexarImagem()
    }

    class Coleta {
        +DateTime data_reserva
        +Boolean confirmacao_fabrica
        +Boolean confirmacao_coletor
        +String status
        +reservarResiduo()
        +confirmarRetiradaFabrica()
        +confirmarRetiradaColetor()
        +cancelarColeta()
        +listarHistorico()
    }

    class Dashboard {
        +Double total_residuos_reaproveitados
        +calcularImpacto()
    }

    %% Relacionamentos
    Usuario "1" -- "*" Endereco : possui
    Usuario "1" -- "*" OfertaResiduo : publica (como Fábrica)
    Usuario "1" -- "*" Coleta : realiza (como Coletor)
    
    OfertaResiduo "*" -- "1" Material : categorizada por
    OfertaResiduo "1" -- "1" Endereco : localizada em
    OfertaResiduo "1" -- "*" OfertaImagem : possui fotos
    OfertaResiduo "1" -- "0..1" Coleta : é alvo de

```

## Funcionalidades e Requisitos Atendidos

- **Cadastro de Usuários e Perfis (RF01) / Login (RF02)**: Mapeados na classe `Usuario`.
- **Cadastro de Oferta (RF03) e Imagens**: Mapeados nas classes `OfertaResiduo` e `OfertaImagem`.
- **Mural de Resíduos (RF04) e Filtros (RF09)**: Funcionalidades base na classe `OfertaResiduo` associada a `Material`.
- **Reserva de Coleta (RF05) e Confirmações (RF06)**: Fluxo de negócio gerenciado pela classe `Coleta` que interliga os perfis.
- **Histórico (RF07)**: Funcionalidade de listar operações realizadas abstraída em `Coleta`.
- **Gerenciamento de Endereços (RF08)**: Representado pela classe `Endereco`.
- **Dashboard de Impacto (RF10)**: Abstraído na classe de conceito `Dashboard`.

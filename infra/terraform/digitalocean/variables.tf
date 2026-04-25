variable "project_name" {
  description = "Nome logico do ambiente ou stack."
  type        = string
}

variable "environment" {
  description = "Ambiente de deploy."
  type        = string
}

variable "do_token" {
  description = "Token da API da DigitalOcean."
  type        = string
  sensitive   = true
}

variable "region" {
  description = "Regiao padrao da DigitalOcean."
  type        = string
  default     = "nyc3"
}

variable "vpc_uuid" {
  description = "UUID da VPC existente na DigitalOcean."
  type        = string
}

variable "ssh_key_ids" {
  description = "Lista de IDs das chaves SSH cadastradas na DigitalOcean."
  type        = list(string)
  default     = []
}

variable "allowed_ssh_cidrs" {
  description = "CIDRs autorizados para SSH."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "allowed_http_cidrs" {
  description = "CIDRs autorizados para HTTP e HTTPS."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "common_tags" {
  description = "Tags adicionais aplicadas aos recursos."
  type        = list(string)
  default     = []
}

variable "droplets" {
  description = "Mapa de droplets a criar."
  type = map(object({
    name            = string
    size            = string
    image           = string
    backups         = optional(bool, false)
    monitoring      = optional(bool, true)
    ipv6            = optional(bool, false)
    user_data       = optional(string, null)
    volume_size_gib = optional(number, 0)
  }))
}

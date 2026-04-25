variable "project_name" {
  description = "Nome do projeto."
  type        = string
}

variable "environment" {
  description = "Ambiente de deploy."
  type        = string
}

variable "pm_api_url" {
  description = "URL da API do Proxmox."
  type        = string
}

variable "pm_api_token_id" {
  description = "Token ID da API do Proxmox."
  type        = string
}

variable "pm_api_token_secret" {
  description = "Segredo do token da API do Proxmox."
  type        = string
  sensitive   = true
}

variable "pm_tls_insecure" {
  description = "Ignora validacao TLS da API do Proxmox."
  type        = bool
  default     = true
}

variable "target_node" {
  description = "No do Proxmox onde os recursos serao criados."
  type        = string
}

variable "bridge" {
  description = "Bridge de rede a ser usada."
  type        = string
  default     = "vmbr0"
}

variable "storage_pool" {
  description = "Storage principal para discos e containers."
  type        = string
  default     = "local-lvm"
}

variable "snippets_storage" {
  description = "Storage com snippets cloud-init."
  type        = string
  default     = "local"
}

variable "vm_clone_template" {
  description = "Nome da VM template base para clone."
  type        = string
}

variable "lxc_template_file" {
  description = "Template LXC existente no storage."
  type        = string
}

variable "ssh_public_keys" {
  description = "Chaves publicas SSH para cloud-init e LXC."
  type        = string
}

variable "gateway" {
  description = "Gateway padrao da rede."
  type        = string
}

variable "dns_servers" {
  description = "Servidores DNS para VMs e LXCs."
  type        = string
  default     = "1.1.1.1 8.8.8.8"
}

variable "vm_definitions" {
  description = "Mapa de VMs a provisionar."
  type = map(object({
    vmid          = number
    name          = string
    cores         = number
    sockets       = number
    memory        = number
    disk_size     = string
    ip_address    = string
    ci_user       = optional(string, "ubuntu")
    ci_password   = optional(string, null)
  }))
}

variable "lxc_definitions" {
  description = "Mapa de containers LXC a provisionar."
  type = map(object({
    vmid         = number
    hostname     = string
    cores        = number
    memory       = number
    swap         = number
    disk_size    = string
    ip_address   = string
    password     = string
    unprivileged = optional(bool, true)
  }))
}

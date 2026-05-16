variable "project_name" {
  description = "Nome do projeto."
  type        = string
}

variable "environment" {
  description = "Ambiente de deploy."
  type        = string
}

variable "aws_region" {
  description = "Regiao AWS."
  type        = string
  default     = "us-east-1"
}

variable "vpc_cidr" {
  description = "CIDR da VPC."
  type        = string
  default     = "10.30.0.0/16"
}

variable "availability_zones" {
  description = "Availability zones usadas na VPC."
  type        = list(string)
  default     = ["us-east-1a", "us-east-1b"]
}

variable "public_subnet_cidrs" {
  description = "CIDRs das subnets publicas."
  type        = list(string)
  default     = ["10.30.1.0/24", "10.30.2.0/24"]
}

variable "private_subnet_cidrs" {
  description = "CIDRs das subnets privadas."
  type        = list(string)
  default     = ["10.30.11.0/24", "10.30.12.0/24"]
}

variable "allowed_ssh_cidrs" {
  description = "CIDRs autorizados para acesso SSH."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "allowed_http_cidrs" {
  description = "CIDRs autorizados para HTTP e HTTPS."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "key_pair_name" {
  description = "Nome do key pair da AWS para EC2."
  type        = string
}

variable "ec2_instances" {
  description = "Mapa de instancias EC2."
  type = map(object({
    ami_id                = string
    instance_type         = string
    subnet_type           = string
    associate_public_ip   = optional(bool, false)
    root_volume_size_gib  = optional(number, 40)
    user_data             = optional(string, null)
  }))
}

variable "rds_instance_class" {
  description = "Classe da instancia RDS."
  type        = string
  default     = "db.t4g.medium"
}

variable "rds_allocated_storage" {
  description = "Armazenamento inicial do RDS em GiB."
  type        = number
  default     = 100
}

variable "rds_db_name" {
  description = "Nome inicial do banco PostgreSQL."
  type        = string
  default     = "erp_central"
}

variable "rds_username" {
  description = "Usuario master do RDS."
  type        = string
}

variable "rds_password" {
  description = "Senha master do RDS."
  type        = string
  sensitive   = true
}

variable "redis_node_type" {
  description = "Tipo de no do ElastiCache."
  type        = string
  default     = "cache.t4g.small"
}

variable "redis_engine_version" {
  description = "Versao do Redis."
  type        = string
  default     = "7.1"
}

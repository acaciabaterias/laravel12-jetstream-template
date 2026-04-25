output "vpc_id" {
  description = "ID da VPC principal."
  value       = aws_vpc.this.id
}

output "public_subnet_ids" {
  description = "IDs das subnets publicas."
  value       = [for subnet in aws_subnet.public : subnet.id]
}

output "private_subnet_ids" {
  description = "IDs das subnets privadas."
  value       = [for subnet in aws_subnet.private : subnet.id]
}

output "ec2_instance_ids" {
  description = "IDs das instancias EC2."
  value       = { for key, instance in aws_instance.this : key => instance.id }
}

output "ec2_public_ips" {
  description = "IPs publicos das instancias EC2."
  value       = { for key, instance in aws_instance.this : key => instance.public_ip }
}

output "ec2_private_ips" {
  description = "IPs privados das instancias EC2."
  value       = { for key, instance in aws_instance.this : key => instance.private_ip }
}

output "rds_endpoint" {
  description = "Endpoint do RDS PostgreSQL."
  value       = aws_db_instance.postgres.endpoint
}

output "rds_address" {
  description = "Endereco do RDS PostgreSQL."
  value       = aws_db_instance.postgres.address
}

output "redis_endpoint" {
  description = "Endpoint principal do Redis."
  value       = aws_elasticache_cluster.redis.cache_nodes[0].address
}

output "redis_port" {
  description = "Porta do Redis."
  value       = aws_elasticache_cluster.redis.port
}

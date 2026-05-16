locals {
  common_tags = {
    Project     = var.project_name
    Environment = var.environment
    ManagedBy   = "Terraform"
  }
}

resource "aws_vpc" "this" {
  cidr_block           = var.vpc_cidr
  enable_dns_support   = true
  enable_dns_hostnames = true

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-vpc"
  })
}

resource "aws_internet_gateway" "this" {
  vpc_id = aws_vpc.this.id

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-igw"
  })
}

resource "aws_subnet" "public" {
  for_each = {
    for index, cidr in var.public_subnet_cidrs : index => {
      cidr = cidr
      az   = var.availability_zones[index]
    }
  }

  vpc_id                  = aws_vpc.this.id
  cidr_block              = each.value.cidr
  availability_zone       = each.value.az
  map_public_ip_on_launch = true

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-public-${each.key}"
    Tier = "public"
  })
}

resource "aws_subnet" "private" {
  for_each = {
    for index, cidr in var.private_subnet_cidrs : index => {
      cidr = cidr
      az   = var.availability_zones[index]
    }
  }

  vpc_id            = aws_vpc.this.id
  cidr_block        = each.value.cidr
  availability_zone = each.value.az

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-private-${each.key}"
    Tier = "private"
  })
}

resource "aws_route_table" "public" {
  vpc_id = aws_vpc.this.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.this.id
  }

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-public-rt"
  })
}

resource "aws_route_table_association" "public" {
  for_each = aws_subnet.public

  subnet_id      = each.value.id
  route_table_id = aws_route_table.public.id
}

resource "aws_security_group" "app" {
  name        = "${var.project_name}-${var.environment}-app-sg"
  description = "Acesso para ERP Core e microservicos"
  vpc_id      = aws_vpc.this.id

  ingress {
    description = "SSH"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = var.allowed_ssh_cidrs
  }

  ingress {
    description = "HTTP"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = var.allowed_http_cidrs
  }

  ingress {
    description = "HTTPS"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = var.allowed_http_cidrs
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-app-sg"
  })
}

resource "aws_security_group" "rds" {
  name        = "${var.project_name}-${var.environment}-rds-sg"
  description = "Acesso PostgreSQL para EC2"
  vpc_id      = aws_vpc.this.id

  ingress {
    description     = "PostgreSQL from app SG"
    from_port       = 5432
    to_port         = 5432
    protocol        = "tcp"
    security_groups = [aws_security_group.app.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-rds-sg"
  })
}

resource "aws_security_group" "redis" {
  name        = "${var.project_name}-${var.environment}-redis-sg"
  description = "Acesso Redis para EC2"
  vpc_id      = aws_vpc.this.id

  ingress {
    description     = "Redis from app SG"
    from_port       = 6379
    to_port         = 6379
    protocol        = "tcp"
    security_groups = [aws_security_group.app.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-redis-sg"
  })
}

resource "aws_instance" "this" {
  for_each = var.ec2_instances

  ami                         = each.value.ami_id
  instance_type               = each.value.instance_type
  key_name                    = var.key_pair_name
  subnet_id                   = each.value.subnet_type == "public" ? values(aws_subnet.public)[0].id : values(aws_subnet.private)[0].id
  vpc_security_group_ids      = [aws_security_group.app.id]
  associate_public_ip_address = try(each.value.associate_public_ip, false)
  user_data                   = try(each.value.user_data, null)

  root_block_device {
    volume_size = try(each.value.root_volume_size_gib, 40)
    volume_type = "gp3"
    encrypted   = true
  }

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-${each.key}"
    Role = each.key
  })
}

resource "aws_db_subnet_group" "this" {
  name       = "${var.project_name}-${var.environment}-db-subnets"
  subnet_ids = [for subnet in aws_subnet.private : subnet.id]

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-db-subnets"
  })
}

resource "aws_db_instance" "postgres" {
  identifier              = "${var.project_name}-${var.environment}-postgres"
  engine                  = "postgres"
  engine_version          = "16.3"
  instance_class          = var.rds_instance_class
  allocated_storage       = var.rds_allocated_storage
  max_allocated_storage   = var.rds_allocated_storage * 2
  storage_type            = "gp3"
  storage_encrypted       = true
  db_name                 = var.rds_db_name
  username                = var.rds_username
  password                = var.rds_password
  port                    = 5432
  multi_az                = true
  publicly_accessible     = false
  skip_final_snapshot     = true
  backup_retention_period = 7
  deletion_protection     = false
  db_subnet_group_name    = aws_db_subnet_group.this.name
  vpc_security_group_ids  = [aws_security_group.rds.id]

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-postgres"
  })
}

resource "aws_elasticache_subnet_group" "this" {
  name       = "${var.project_name}-${var.environment}-redis-subnets"
  subnet_ids = [for subnet in aws_subnet.private : subnet.id]
}

resource "aws_elasticache_cluster" "redis" {
  cluster_id           = "${var.project_name}-${var.environment}-redis"
  engine               = "redis"
  engine_version       = var.redis_engine_version
  node_type            = var.redis_node_type
  num_cache_nodes      = 1
  port                 = 6379
  parameter_group_name = "default.redis7"
  subnet_group_name    = aws_elasticache_subnet_group.this.name
  security_group_ids   = [aws_security_group.redis.id]

  tags = merge(local.common_tags, {
    Name = "${var.project_name}-${var.environment}-redis"
  })
}

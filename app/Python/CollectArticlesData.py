from Class.ArticleDataCollector import ArticleDataCollector
from dotenv import load_dotenv
import os

load_dotenv()

# Database configuration
db_config = {
    'host': os.getenv('DB_HOST'),
    'user': os.getenv('DB_USERNAME'),
    'password': os.getenv('DB_PASSWORD'),
    'database': os.getenv('DB_DATABASE'),
    'port': os.getenv('DB_PORT', 3306)
}

email = "your_email@example.com"

# Create an instance of the CollectArticlesData class
collector = ArticleDataCollector(db_config, email)


# Run the data collection and processing
collector.run()

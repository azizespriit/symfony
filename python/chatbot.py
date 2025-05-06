import os
from openai import OpenAI
from dotenv import load_dotenv

# Charger les variables d'environnement
load_dotenv()

# Récupérer la clé API depuis .env
api_key = os.getenv("OPENAI_API_KEY")

client = OpenAI(api_key=api_key)

def chat_with_openai(user_message):
    completion = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[
            {"role": "user", "content": user_message}
        ]
    )
    return completion.choices[0].message.content

if __name__ == "__main__":
    while True:
        user_input = input("Vous : ")
        if user_input.lower() in ["exit", "quit"]:
            print("Au revoir ! 👋")
            break
        response = chat_with_openai(user_input)
        print("Bot :", response)
